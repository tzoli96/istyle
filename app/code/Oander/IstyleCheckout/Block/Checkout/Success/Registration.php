<?php

namespace Oander\IstyleCheckout\Block\Checkout\Success;

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
}