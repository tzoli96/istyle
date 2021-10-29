<?php

namespace Oney\ThreeByFour\Api;

use Magento\Quote\Api\Data\CartInterface;

interface FacilypayMethodInterface
{
    /**
     * @return integer
     */
    public function getMaxOrderTotal();
}
