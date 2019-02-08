<?php
declare(strict_types=1);

namespace Oander\ApplePay\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Oander\ApplePay\Enum\DisplayIn;

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
        return $this->getUrl(\Oander\ApplePay\Controller\Ajax\PaymentData::ROUTE);
    }

    public function getMerchantId()
    {
        return $this->paymentConfig->getMerchantId();
    }

    /**
     * Braintree's API token
     * @return string|null
     */
    public function getClientToken()
    {
        return $this->paymentConfig->getClientToken();
    }

    /**
     * Merchant name to display in popup
     * @return string
     */
    public function getMerchantName()
    {
        return $this->paymentConfig->getMerchantName();
    }

    /**
     * URL To success page
     * @return string
     */
    public function getActionSuccess()
    {
        return $this->getUrl('checkout/onepage/success', ['_secure' => true]);
    }

    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    public function getQuoteDetailsURL()
    {
        return $this->getUrl(\Oander\ApplePay\Controller\Ajax\PaymentData::ROUTE);
    }

    public function getCountryCode()
    {
        return $this->paymentConfig->getCountryCode();
    }

    public function getCurrencyCode()
    {
        return $this->paymentConfig->getCurrencyCode();
    }

    public function getShowInCart()
    {
        return $this->paymentConfig->getShowInCart();
    }

    public function getShowInCheckout()
    {
        return $this->paymentConfig->getShowInCheckout();
    }

    public function getShowInMiniCart()
    {
        return $this->paymentConfig->getShowInMiniCart();
    }

    public function getShowOnProductPage()
    {
        return $this->paymentConfig->getShowOnProductPage();
    }

    public function getDisplayIn()
    {
        $displayIn = [];
        if($this->getShowInCart())
            $displayIn[] = DisplayIn::CART;
        if($this->getShowInMiniCart())
            $displayIn[] = DisplayIn::MINICART;
        if($this->getShowOnProductPage())
            $displayIn[] = DisplayIn::PRODUCTPAGE;
        if($this->getShowInCheckout())
            $displayIn[] = DisplayIn::CHECKOUT;
        return $displayIn;
    }

    /*public function getJsonConfig()
    {
        $config = $this->paymentConfig->getPaymentAllConfig();
        $config['quoteDetailsURL'] = $this->getUrl(\Oander\ApplePay\Controller\Ajax\PaymentData::ROUTE);
        $config['placeOrderURL'] = $this->getUrl(\Oander\ApplePay\Controller\Ajax\Payment::ROUTE);
        $config['version'] = 4;
        $config['countryCode'] = $this->paymentConfig->getCountryCode();
        $config['languageCode'] = $this->paymentConfig->getLanguageCode();
        $config['currencyCode'] = $this->paymentConfig->getCurrencyCode();
        $config['clientToken'] = $this->paymentConfig->getClientToken();
        return \Zend_Json::encode($config);
    }*/
}
