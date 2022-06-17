<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\CustomerExtend\Plugin\Magento\Quote\Model;

class BillingAddressManagement
{

    public function beforeAssign(
        \Magento\Quote\Model\BillingAddressManagement $subject,
        $cartId,
        $address,
        $useForShipping = false
    )
    {
        $extAttributes = $address->getExtensionAttributes();
        if (!empty($extAttributes)) {
            $address->setIsCompany($extAttributes->getIsCompany());
        }
        return [$cartId, $address, $useForShipping];
    }
}