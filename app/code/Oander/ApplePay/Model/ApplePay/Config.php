<?php

namespace Oander\ApplePay\Model\ApplePay;

/**
 * Class Config
 * @package Oander\ApplePay\Model\ApplePay
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    /**
     * Get merchant name to display
     * @return string
     */
    public function getMerchantName()
    {
        return $this->getValue('merchant_name');
    }
}
