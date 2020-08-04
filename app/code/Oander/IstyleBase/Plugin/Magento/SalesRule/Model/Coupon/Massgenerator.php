<?php
/**
 * A Magento 2 module named Oander/IstyleBase
 * Copyright (C) 2019
 *
 * This file included in Oander/IstyleBase is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

/**
 * ISSUE 45310
 */

namespace Oander\IstyleBase\Plugin\Magento\SalesRule\Model\Coupon;

class Massgenerator
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry
    )
    {
        $this->_coreRegistry = $coreRegistry;
    }

    public function beforeGeneratePool(
        \Magento\SalesRule\Model\Coupon\Massgenerator $subject
    ) {
        /** @var $rule \Magento\SalesRule\Model\Rule */
        $rule = $this->_coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);
        $subject->setUsesPerCoupon($rule->getUsesPerCoupon());
        $subject->setUsagePerCustomer($rule->getUsesPerCustomer());
        return [];
    }
}