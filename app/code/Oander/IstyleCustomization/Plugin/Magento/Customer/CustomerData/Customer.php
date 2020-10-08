<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\IstyleCustomization\Plugin\Magento\Customer\CustomerData;

class Customer
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    private $currentCustomer;
    /**
     * @var \Oander\NameSwitcher\Helper\Switching
     */
    private $switchingHelper;

    /**
     * Customer constructor.
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Oander\NameSwitcher\Helper\Switching $switchingHelper
     */
    public function __construct(
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Oander\NameSwitcher\Helper\Switching $switchingHelper
    )
    {
        $this->currentCustomer = $currentCustomer;
        $this->switchingHelper = $switchingHelper;
    }

    public function afterGetSectionData(
        \Magento\Customer\CustomerData\Customer $subject,
        $result
    ) {
        if (!$this->currentCustomer->getCustomerId()) {
            return $result;
        }
        $monogramSection = [
            'monogram' => $this->getCustomerMonogram(),
        ];
        $result = array_merge_recursive($result, $monogramSection);
        return $result;
    }

    /**
     * @return string
     */
    protected function getCustomerMonogram(): string
    {
        $customer = $this->currentCustomer->getCustomer();
        $firstNameChar = strtoupper(mb_substr($customer->getFirstname(), 0, 1));
        $lastNameChar = strtoupper(mb_substr($customer->getLastname(), 0, 1));

        if ($this->switchingHelper->isInverted()
            && $this->switchingHelper->isThirdPartyModulesInverted()
        ) {
            $monogram = $lastNameChar . $firstNameChar;
        } else {
            $monogram = $firstNameChar . $lastNameChar;
        }

        return $monogram;
    }
}
