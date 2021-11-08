<?php

namespace Oney\ThreeByFour\Observer;

use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Sales\Model\Order;
use Oney\ThreeByFour\Api\Payment\CancelInterface;
use Oney\ThreeByFour\Logger\Logger;

class CancelOrder extends AbstractDataAssignObserver
{
    /**
     * @var CancelInterface
     */
    protected $cancelService;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * AssignData constructor.
     *
     * @param CancelInterface $cancelService
     * @param Logger          $logger
     */
    public function __construct(
        CancelInterface $cancelService,
        Logger $logger
    )
    {
        $this->cancelService = $cancelService;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getOrder();
        if(strpos($order->getPayment()->getMethod(), "facilypay") !== false) {
            $this->logger->info("Cancel Order Oney Observer :: ". $order->getId());
            $this->cancelService->cancel($order);
        }
    }
}
