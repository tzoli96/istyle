<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\CustomerExtend\Plugin\Magento\Quote\Model\Quote\Address;

class ToOrderAddress
{

    public function aroundConvert(
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject,
        \Closure $proceed,
        $object,
        $data = []
    ) {
        $result = $proceed($object, $data);
        $result->setIsCompany($object->getIsCompany());
        return $result;
    }
}