<?php

namespace Oney\ThreeByFour\Api\Payment;

use Magento\Sales\Api\Data\OrderInterface;

interface ConfirmInterface
{
    /**
     * @param OrderInterface $order
     * @return mixed
     */
    public function confirm(OrderInterface $order);
}
