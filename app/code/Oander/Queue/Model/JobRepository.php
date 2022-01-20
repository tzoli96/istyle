<?php


namespace Oander\Queue\Model;

use Oander\Queue\Model\ResourceModel\Job as ResourceJob;
use Oander\Queue\Model\ResourceModel\Job\CollectionFactory as JobCollectionFactory;
use Oander\Queue\Api\Data\JobInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Oander\Queue\Api\JobRepositoryInterface;
use Oander\Queue\Api\Data\JobSearchResultsInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Store\Model\StoreManagerInterface;

class JobRepository implements JobRepositoryInterface
{

    protected $jobCollectionFactory;

    protected $dataObjectHelper;

    protected $extensibleDataObjectConverter;
    private $collectionProcessor;

    private $storeManager;

    protected $searchResultsFactory;

    protected $resource;

    protected $dataJobFactory;

    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    protected $jobFactory;


    /**
     * @param ResourceJob $resource
     * @param JobFactory $jobFactory
     * @param JobInterfaceFactory $dataJobFactory
     * @param JobCollectionFactory $jobCollectionFactory
     * @param JobSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceJob $resource,
        JobFactory $jobFactory,
        JobInterfaceFactory $dataJobFactory,
        JobCollectionFactory $jobCollectionFactory,
        JobSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->jobFactory = $jobFactory;
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataJobFactory = $dataJobFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Oander\Queue\Api\Data\JobInterface $job
    ) {
        /* if (empty($job->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $job->setStoreId($storeId);
        } */
        
        $jobData = $this->extensibleDataObjectConverter->toNestedArray(
            $job,
            [],
            \Oander\Queue\Api\Data\JobInterface::class
        );
        
        $jobModel = $this->jobFactory->create()->setData($jobData);
        
        try {
            $this->resource->save($jobModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the job: %1',
                $exception->getMessage()
            ));
        }
        return $jobModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($jobId)
    {
        $job = $this->jobFactory->create();
        $this->resource->load($job, $jobId);
        if (!$job->getId()) {
            throw new NoSuchEntityException(__('Job with id "%1" does not exist.', $jobId));
        }
        return $job->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getByName($jobName)
    {
        $job = $this->jobFactory->create();
        $this->resource->load($job, $jobName, \Oander\Queue\Api\Data\JobInterface::NAME);
        if (!$job->getId()) {
            throw new NoSuchEntityException(__('Job with name "%1" does not exist.', $jobName));
        }
        return $job->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->jobCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Oander\Queue\Api\Data\JobInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Oander\Queue\Api\Data\JobInterface $job
    ) {
        try {
            $jobModel = $this->jobFactory->create();
            $this->resource->load($jobModel, $job->getJobId());
            $this->resource->delete($jobModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Job: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($jobId)
    {
        return $this->delete($this->getById($jobId));
    }
}
