<?php
/**
 * Loan Payment modul for Cofidis
 * Copyright (C) 2019 
 * 
 * This file included in Oander/CofidisPayment is licensed under OSL 3.0
 * 
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Oander\CofidisPayment\Model\Payment;

use Oander\CofidisPayment\Enum\Config as EnumConfig;

class Cofidis extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "cofidis";
    protected $_isOffline = true;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        if( ($quote->getGrandTotal() >= $this->getConfigData(EnumConfig::MINIMUM_TOTAL)) && ($quote->getGrandTotal() <= $this->getConfigData(EnumConfig::MAXIMUM_TOTAL)) )
        {
            return parent::isAvailable($quote);
        }
        return false;
    }

    /**
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return \Oander\CofidisPayment\Helper\Config::REDIRECT_URL;
        //return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);$this->getConfigData(EnumConfig::REDIRECT_URL);
    }
}
