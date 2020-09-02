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

use Oander\CofidisPayment\Helper\Config;
use Oander\CofidisPayment\Helper\Data;

class Cofidis extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * @var Config
     */
    private $config;
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Oander\CofidisPayment\Helper\Config $config,
        \Oander\CofidisPayment\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $resource, $resourceCollection, $data);
        $this->config = $config;
        $this->helper = $helper;
    }

    protected $_code = "cofidis";
    protected $_isOffline = true;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        $hasValidBarem = false;
        $ownshares = $this->config->getOwnshares();
        foreach ($ownshares as $ownshare)
        {
            $hasValidBarem |= $this->helper->isAllowedByMinimumTotalAmount($quote->getGrandTotal(), $ownshare);
        }
        if($hasValidBarem)
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
    }
}
