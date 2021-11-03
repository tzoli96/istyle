<?php
/**
 * dqwqe
 * Copyright (C) 2019
 *
 * This file is part of Oander/IstyleCheckout.
 *
 * Oander/IstyleCheckout is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Oander\IstyleCheckout\Plugin\Frontend\Magento\Customer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;

class Address
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Address constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function afterUpdateData(
        \Magento\Customer\Model\Address $subject,
        $result
    ) {
        if($this->scopeConfig->isSetFlag("customer/create_account/vat_frontend_visibility", \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->scopeConfig->isSetFlag("customer/address/taxvat_profile_checkout_required", \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
        {
            if (!\Zend_Validate::is($result->getVatId(), 'NotEmpty')) {
                $exception = new InputException();
                $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'vatid']));
                throw $exception;
            }
        }
        return $result;
    }
}