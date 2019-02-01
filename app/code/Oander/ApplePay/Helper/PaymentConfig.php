<?php

declare(strict_types=1);

namespace Oander\ApplePay\Helper;

use Braintree\Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Oander\ApplePay\Enum\PaymentConfig as PaymentConfigEnum;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Oander\DropdownProducts\Enum\Config as ConfigEnum;

/**
 * Class Config
 * @package Oander\ApplePay\Helper
 */
class PaymentConfig extends AbstractHelper
{
    /**
     * @var array
     */
    protected $payment;

    /**
     * @var array
     */
    protected $sales;

    /**
     * @var array
     */
    protected $locale;

    /**
     * @var array
     */
    protected $currency;

    /**
     * @var BraintreeAdapter
     */
    protected $braintreeAdapter;

    /**
     * @var string
     */
    protected $clientToken = '';

    /**
     * Config constructor.
     *
     * @param Context          $context
     * @param BraintreeAdapter $braintreeAdapter
     */
    public function __construct(
        Context $context,
        BraintreeAdapter $braintreeAdapter
    ) {
        parent::__construct($context);

        $this->payment = (array)$this->scopeConfig->getValue(
            PaymentConfigEnum::PAYMENT_SETTINGS_PATH,
            ScopeInterface::SCOPE_STORE
        );

        $this->sales = (array)$this->scopeConfig->getValue(
            'shipping/origin',
            ScopeInterface::SCOPE_STORE
        );

        $this->locale = (array)$this->scopeConfig->getValue(
            'general/locale',
            ScopeInterface::SCOPE_STORE
        );

        $this->currency = (array)$this->scopeConfig->getValue(
            'currency/options',
            ScopeInterface::SCOPE_STORE
        );

        $this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_ENABLED_SHIPPING_METHODS] = $this->explodeconfig(isset($this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_ENABLED_SHIPPING_METHODS])?$this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_ENABLED_SHIPPING_METHODS]:null);
        $this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_SPECIFICCOUNTRY] = $this->explodeconfig(isset($this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_SPECIFICCOUNTRY])?$this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_SPECIFICCOUNTRY]:null);
        $this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_MERCHANT_CAPABILITIES] = $this->explodeconfig(isset($this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_MERCHANT_CAPABILITIES])?$this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_MERCHANT_CAPABILITIES]:null);
        $this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_SUPPORTED_NETWORKS] = $this->explodeconfig(isset($this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_SUPPORTED_NETWORKS])?$this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_SUPPORTED_NETWORKS]:null);
        $this->braintreeAdapter = $braintreeAdapter;
    }

    public function getPaymentAllConfig()
    {
        return $this->payment;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_ACTIVE];
    }

    /**
     * @return string|null
     */
    public function getMerchantId()
    {
        return $this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_MERCHANT_ID];
    }

    /**
     * @return string|null
     */
    public function getLabelOnPaymentSheet()
    {
        return $this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_TITLE];
    }

    /**
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->sales['country_id'];
    }

    /**
     * @return string|null
     */
    public function getLanguageCode()
    {
        return $this->locale['code'];
    }

    /**
     * @return string|null
     */
    public function getCurrencyCode()
    {
        return $this->currency['base'];
    }

    /**
     * @return bool
     */
    public function getShowOnProductPage()
    {
        return (bool)$this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_SHOW_PRODUCT_PAGE];
    }

    /**
     * @return bool
     */
    public function getShowInMiniCart()
    {
        return (bool)$this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_SHOW_MINI_CART];
    }

    /**
     * @return bool
     */
    public function getShowInCart()
    {
        return (bool)$this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_SHOW_CART];
    }

    /**
     * @return bool
     */
    public function getShowInCheckout()
    {
        return (bool)$this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_SHOW_CHECKOUT];
    }

    /**
     * @return array
     */
    public function getEnabledShippingMethods()
    {
        return $this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_ENABLED_SHIPPING_METHODS];
    }

    /**
     * @return array
     */
    public function getAllowedCountries()
    {
        return $this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_SPECIFICCOUNTRY];
    }

    /**
     * @return array
     */
    public function getMerchantCapabilities()
    {
        return $this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_MERCHANT_CAPABILITIES];
    }

    /**
     * @return array
     */
    public function getSupportedNetworks()
    {
        return $this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_SUPPORTED_NETWORKS];
    }

    /**
     * @return mixed
     */
    public function getDefaultCountryId()
    {
        return $this->scopeConfig->getValue(
            \Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_GENERAL_COUNTRY_DEFAULT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Generate a new client token if necessary
     * @return string
     */
    public function getClientToken()
    {
        if (empty($this->clientToken)) {
            $this->clientToken = $this->braintreeAdapter->generate();
        }

        return $this->clientToken;
    }

    /**
     * @return mixed
     */
    public function getMerchantName()
    {
        return (string)$value = $this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_MERCHANT_NAME] ?? '';
    }

    /**
     * @return mixed
     */
    public function getPaymentAction()
    {
        return (int)$value = $this->payment[PaymentConfigEnum::PAYMENT_SETTINGS_PAYMENT_ACTION] ?? 0;
    }

    /**
     * @param $config string
     * @return array
     */
    private function explodeconfig($config)
    {
        if($config===null)
            return [];
        try
        {
            return explode(',', $config);
        }
        catch (\Exception $e)
        {
            return [];
        }
    }
}