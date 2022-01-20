<?php


namespace Oander\Queue\Model;

use Oander\Queue\Api\Data\LogInterface;
use Oander\Queue\Api\Data\LogInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Log extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'oander_queue_log';
    protected $logDataFactory;

    protected $dataObjectHelper;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param LogInterfaceFactory $logDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Oander\Queue\Model\ResourceModel\Log $resource
     * @param \Oander\Queue\Model\ResourceModel\Log\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        LogInterfaceFactory $logDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Oander\Queue\Model\ResourceModel\Log $resource,
        \Oander\Queue\Model\ResourceModel\Log\Collection $resourceCollection,
        array $data = []
    ) {
        $this->logDataFactory = $logDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve log model with log data
     * @return LogInterface
     */
    public function getDataModel()
    {
        $logData = $this->getData();
        
        $logDataObject = $this->logDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $logDataObject,
            $logData,
            LogInterface::class
        );
        
        return $logDataObject;
    }
}
