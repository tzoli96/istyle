<?php

namespace Oander\SalesforceLoyalty\Observer\Sales;

use Oander\SalesforceLoyalty\Enum\Attribute;

class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Oander\Queue\Helper\JobManager
     */
    private $jobManager;
    /**
     * @var \Oander\SalesforceLoyalty\Model\PointFreeQueueClass
     */
    private $pointFreeQueueClass;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Oander\Queue\Helper\JobManager $jobManager
     * @param \Oander\SalesforceLoyalty\Model\PointFreeQueueClass $pointFreeQueueClass
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Oander\Queue\Helper\JobManager $jobManager,
        \Oander\SalesforceLoyalty\Model\PointFreeQueueClass $pointFreeQueueClass
    )
    {
        $this->jobManager = $jobManager;
        $this->pointFreeQueueClass = $pointFreeQueueClass;
        $this->logger = $logger;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof \Magento\Framework\Model\AbstractModel) {
            try {
                if ($order->getState() == 'canceled' || $order->getOrigData("state") != 'canceled') {
                    if (is_string($order->getData(Attribute::LOYALTY_BLOCK_TRANSACTION_ID))) {
                        $this->pointFreeQueueClass->setData([
                            \Oander\SalesforceLoyalty\Model\PointFreeQueueClass::DATA_TRANSACTIONID => $order->getData(Attribute::LOYALTY_BLOCK_TRANSACTION_ID),
                            \Oander\SalesforceLoyalty\Model\PointFreeQueueClass::DATA_ORDERINCREMENTID => $order->getIncrementId(),
                            \Oander\SalesforceLoyalty\Model\PointFreeQueueClass::DATA_COUNTRYCODE => $order->getStore()->getCode()
                        ]);
                        $this->jobManager->addJobClass($this->pointFreeQueueClass);
                    }
                }
            }
            catch (\Exception $e)
            {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}