<?php
declare(strict_types=1);

namespace Oander\ApplePay\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Dashboard
 * @package Oander\ApplePay\Block
 */
class Main extends Template
{
    /**
     * @var \Oander\ApplePay\Helper\PaymentConfig
     */
    private $paymentConfig;

    /**
     * Main constructor.
     * @param Context $context
     * @param \Oander\ApplePay\Helper\PaymentConfig $paymentConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Oander\ApplePay\Helper\PaymentConfig $paymentConfig,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->paymentConfig = $paymentConfig;
    }

    public function getPaymentConfig()
    {
        return $this->paymentConfig;
    }

    public function getQuoteURL()
    {
        return $this->getUrl(\Oander\ApplePay\Controller\Ajax\GenerateQuote::ROUTE);
    }

    public function getMerchantId()
    {
        return $this->paymentConfig->getMerchantId();
    }

    public function getJsonConfig()
    {
        $config = $this->paymentConfig->getPaymentAllConfig();
        $config['quoteDetailsURL'] = $this->getUrl(\Oander\ApplePay\Controller\Ajax\GenerateQuote::ROUTE);
        $config['version'] = 4;
        $config['countryCode'] = $this->paymentConfig->getCountryCode();
        $config['languageCode'] = $this->paymentConfig->getLanguageCode();
        $config['currencyCode'] = $this->paymentConfig->getCurrencyCode();
        $config['clientToken'] = $this->paymentConfig->getClientToken();
        $config['merchantName'] = $this->paymentConfig->getMerchantName();
        $config['merchantId'] = $this->paymentConfig->getMerchantId();
        return \Zend_Json::encode($config);
    }
}
