<?php

namespace Oney\ThreeByFour\Api;

use Magento\Payment\Model\MethodInterface;

interface PaymentMethodListInterface
{
    /**
     * @param string $method
     *
     * @return MethodInterface
     */
    public function getPaymentMethod($method);
}
