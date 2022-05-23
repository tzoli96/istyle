<?php

namespace Oander\RaiffeisenPayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const PAYMENT_METHOD_PATH = 'payment/raiffeisen/';
    const ENABLED = 'active';
    const TITLE = 'title';
    const API_URL = 'api_url';
    const API_SUFFIX = 'api_suffix';
    const MERCHANT_NAME = 'merchant_name';
    const MERCHANT_ADDRESS_1 = 'merchant_address_1';
    const MERCHANT_ADDRESS_2 = 'merchant_address_2';
    const POST_CODE = 'pos_code';
    const MERCHANT_REGISTRATION_NUMBER = 'merchant_registration_number';
    const ORDER_EXPIRATION = 'order_expiration';
    const MIN_AMOUNT = 'min_amount';
    const INSTRUCTIONS = 'instructions';
    const ELIGIBILITY_QUESTIONS = 'eligibility_questions';
    const SORT_ORDER = 'sort_order';

    /**
     * @var array
     */
    private $paymentMethod;

    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     * @param $storeid
     * @return bool
     */
    public function getPaymentMethodIsActive($storeid = null)
    {
        return (bool)$value = $this->scopeConfig->getValue(
            self::PAYMENT_METHOD_PATH.self::ENABLED,
            ScopeInterface::SCOPE_STORE
        ) ?? false;
    }

    /**
     * @param $storeid
     * @return string
     */
    public function getTitle($storeid = null)
    {
        return (string)$value = $this->scopeConfig->getValue(
                self::PAYMENT_METHOD_PATH.self::TITLE,
                ScopeInterface::SCOPE_STORE
            ) ?? '';
    }

    /**
     * @param $storeid
     * @return string
     */
    public function getApiUrl($storeid = null)
    {
        return (string)$value = $this->scopeConfig->getValue(
                self::PAYMENT_METHOD_PATH.self::API_URL,
                ScopeInterface::SCOPE_STORE
            ) ?? '';
    }

    /**
     * @param $storeid
     * @return string
     */
    public function getApiSuffix($storeid = null)
    {
        return (string)$value = $this->scopeConfig->getValue(
                self::PAYMENT_METHOD_PATH.self::API_SUFFIX,
                ScopeInterface::SCOPE_STORE
            ) ?? '';
    }

    /**
     * @param $storeid
     * @return string
     */
    public function getMerchantName($storeid = null)
    {
        return (string)$value = $this->scopeConfig->getValue(
                self::PAYMENT_METHOD_PATH.self::MERCHANT_NAME,
                ScopeInterface::SCOPE_STORE
            ) ?? '';
    }

    /**
     * @param $storeid
     * @return string
     */
    public function getMerchantAddress1($storeid = null)
    {
        return (string)$value = $this->scopeConfig->getValue(
                self::PAYMENT_METHOD_PATH.self::MERCHANT_ADDRESS_1,
                ScopeInterface::SCOPE_STORE
            ) ?? '';
    }

    /**
     * @param $storeid
     * @return string
     */
    public function getMerchantAddress2($storeid = null)
    {
        return (string)$value = $this->scopeConfig->getValue(
                self::PAYMENT_METHOD_PATH.self::MERCHANT_ADDRESS_2,
                ScopeInterface::SCOPE_STORE
            ) ?? '';
    }

    /**
     * @param $storeid
     * @return string
     */
    public function getPosCode($storeid = null)
    {
        return (string)$value = $this->scopeConfig->getValue(
                self::PAYMENT_METHOD_PATH.self::POST_CODE,
                ScopeInterface::SCOPE_STORE
            ) ?? '';
    }

    /**
     * @param $storeid
     * @return string
     */
    public function getMerchantRegistrationNumber($storeid = null)
    {
        return (string)$value = $this->scopeConfig->getValue(
                self::PAYMENT_METHOD_PATH.self::MERCHANT_REGISTRATION_NUMBER,
                ScopeInterface::SCOPE_STORE
            ) ?? '';
    }

    /**
     * @param $storeid
     * @return string
     */
    public function getOrderExpiration($storeid = null)
    {
        return (string)$value = $this->scopeConfig->getValue(
                self::PAYMENT_METHOD_PATH.self::ORDER_EXPIRATION,
                ScopeInterface::SCOPE_STORE
            ) ?? '';
    }

    /**
     * @param $storeid
     * @return string
     */
    public function getMinAmount($storeid = null)
    {
        return (string)$value = $this->scopeConfig->getValue(
                self::PAYMENT_METHOD_PATH.self::MIN_AMOUNT,
                ScopeInterface::SCOPE_STORE
            ) ?? '';
    }

    /**
     * @param $storeid
     * @return string
     */
    public function getInstructions($storeid = null)
    {
        return (string)$value = $this->scopeConfig->getValue(
                self::PAYMENT_METHOD_PATH.self::INSTRUCTIONS,
                ScopeInterface::SCOPE_STORE
            ) ?? '';
    }

    /**
     * @param $storeid
     * @return string
     */
    public function getEligibilityQuestions($storeid = null)
    {
        return (string)$value = $this->scopeConfig->getValue(
                self::PAYMENT_METHOD_PATH.self::ELIGIBILITY_QUESTIONS,
                ScopeInterface::SCOPE_STORE
            ) ?? '';
    }

}