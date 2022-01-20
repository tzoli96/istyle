<?php


namespace Oander\Queue\Model;

use Magento\Framework\Api\DataObjectHelper;
use Oander\Queue\Api\Data\JobInterface;
use Oander\Queue\Api\Data\JobInterfaceFactory;

class Job extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'oander_queue_job';
    protected $jobDataFactory;

    protected $dataObjectHelper;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param JobInterfaceFactory $jobDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Oander\Queue\Model\ResourceModel\Job $resource
     * @param \Oander\Queue\Model\ResourceModel\Job\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        JobInterfaceFactory $jobDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Oander\Queue\Model\ResourceModel\Job $resource,
        \Oander\Queue\Model\ResourceModel\Job\Collection $resourceCollection,
        array $data = []
    ) {
        $this->jobDataFactory = $jobDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve job model with job data
     * @return JobInterface
     */
    public function getDataModel()
    {
        $jobData = $this->getData();
        
        $jobDataObject = $this->jobDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $jobDataObject,
            $jobData,
            JobInterface::class
        );
        
        return $jobDataObject;
    }
}
