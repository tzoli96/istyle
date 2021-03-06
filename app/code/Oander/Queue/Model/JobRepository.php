<?php


namespace Oander\Queue\Model;

use Magento\Framework\Api\SortOrder;
use Oander\Queue\Model\ResourceModel\Job as ResourceJob;
use Oander\Queue\Model\ResourceModel\Job\CollectionFactory as JobCollectionFactory;
use Oander\Queue\Api\Data\JobInterfaceFactory;
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
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
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
