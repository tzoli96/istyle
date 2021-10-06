<?php

namespace Oander\SalesforceLoyalty\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Oander\SalesforceLoyalty\Model\PointBlock\Helper;
use Magento\Sales\Model\Order;
use Oander\SalesforceLoyalty\Enum\Attribute;

class PointBlock implements ObserverInterface
{
    /**
     * @var Helper
     */
    protected $clientHelper;

    /**
     * @param Helper $clientHelper
     */
    public function __construct(
        Helper $clientHelper
    )
    {
        $this->clientHelper = $clientHelper;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($order->getData(Attribute::LOYALTY_DISCOUNT)) {
            $response = $this->clientHelper->pointBlock($order->getData(Attribute::LOYALTY_POINT));
            if($response->result->IsSuccess)
            {
                $order->setData(Attribute::LOYALTY_BLOCK_TRANSACTION_ID,$response->result->BlockingTransactionId);
            }
        }

    }
}