<?php


namespace Oney\ThreeByFour\Api\Payment;


use Magento\Sales\Api\Data\OrderInterface;

interface PurchaseInterface
{
    /**
     * @param OrderInterface $order
     * @return mixed
     */
    public function purchase(OrderInterface $order);
}
