<?php

namespace Oander\IstyleCheckout\Block\Checkout\Success;

use Magento\Customer\Model\AccountManagement;
use Oander\IstyleCheckout\Controller\Account\Create;

/**
 * Class Registration
 * @package Oander\IstyleCheckout\Block\OnePage\Success
 */
class Registration extends \Magento\Checkout\Block\Registration
{

    /**
     * Retrieve account creation url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getCreateAccountUrl()
    {
        return $this->getUrl(Create::ROUTE);
    }

    protected function _prepareLayout()
    {
        return $this;
    }

    /**
     * Get minimum password length
     *
     * @return string
     */
    public function getMinimumPasswordLength()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * Get number of password required character classes
     *
     * @return string
     */
    public function getRequiredCharacterClassesNumber()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
    }
}