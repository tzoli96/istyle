<?php

namespace Oander\IstyleCheckout\Plugin\Checkout;

use \Magento\Checkout\Model\Session as CheckoutSession;
use Oander\FanCourierValidator\Helper\Data;
use Oander\IstyleCustomization\Helper\Config;

/**
 * Class LayoutProcessor
 * @package Oander\IstyleCustomization\Plugin\Checkout\Model\Checkout+
 */
class LayoutProcessor
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    private $scopeConfig;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var Data
     */
    private $fanCourierHelper;

    /**
     * LayoutProcessor constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CheckoutSession $checkoutSession
     * @param Config $configHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        Data $fanCourierHelper,
        Config $configHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->fanCourierHelper = $fanCourierHelper;
        $this->configHelper = $configHelper;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        $quoteItems = $this->checkoutSession->getQuote()->getAllItems();
        $dobShow = $this->configHelper->getDobShow($quoteItems);

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['firstname'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['firstname']['sortOrder'] = 1;
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['firstname']['placeholder'] = __('firstname_placeholder');
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['lastname'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['lastname']['sortOrder'] = 2;
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['lastname']['placeholder'] = __('lastname_placeholder');
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['company'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['company']['sortOrder'] = 10;
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['company']['placeholder'] = __('company_placeholder');
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['vat_id'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['vat_id']['sortOrder'] = 11;
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['vat_id']['placeholder'] = __('vat_id_placeholder');
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['pfpj_reg_no'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['pfpj_reg_no']['sortOrder'] = 12;
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['pfpj_reg_no']['placeholder'] = __('pfpj_reg_no_placeholder');
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['postcode'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['postcode']['sortOrder'] = 13;
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['postcode']['placeholder'] = __('postcode_placeholder');
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['city'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['city']['sortOrder'] = 14;
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['city']['placeholder'] = __('city_placeholder');
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['street'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['street']['sortOrder'] = 15;
            
            foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['form-fields']['children']['street']['children'] as $i => &$streetComponent) {
                $streetComponent['placeholder'] = __('street_' . $i . '_placeholder');
            }
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['country_id'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['country_id']['visible'] = false;
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['telephone'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['telephone']['sortOrder'] = 20;
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['telephone']['placeholder'] = __('telephone_placeholder');
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['dob'])) {
            $dobField = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['form-fields']['children']['dob'];

            $dobField['sortOrder'] = 21;
            $dobField['placeholder'] = __('dob_placeholder');
            $dobField['customScope'] = 'shippingAddress.custom_attributes';
            $dobField['dataScope'] = 'shippingAddress.custom_attributes.dob';
            $dobField['visible'] = true;
            $dobField['options']['changeMonth'] = true;
            $dobField['options']['changeYear'] = true;
            $dobField['options']['maxDate'] = '-1d';
            $dobField['options']['yearRange'] = '-100y:c+nn';
            $dobField['validation']['required-entry'] = false;

            if ($dobShow === null) {
                $dobField['visible'] = false;
            } elseif ($dobShow === 'req') {
                $dobField['validation']['required-entry'] = true;
            }

            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['form-fields']['children']['dob'] = $dobField;
        }

        if ($this->fanCourierHelper->getValidationLevel() == 'req' || $this->fanCourierHelper->getValidationLevel() == 'valid') {
            $dataScopePrefix = 'billingAddress';
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['dataScopePrefix'])) {
                $dataScopePrefix = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['dataScopePrefix'];
            } elseif (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['dataScope'])) {
                $dataScopePrefix = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['dataScope'];
            }

            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['form-fields']['children']
            ['region'] = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['form-fields']['children']
            ['region'];
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['form-fields']['children']
            ['region']['dataScope'] = $dataScopePrefix.'.'.'region';
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['form-fields']['children']
            ['region']['config']['customScope'] = $dataScopePrefix;

            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['form-fields']['children']
            ['city'] = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['form-fields']['children']
            ['city'];
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['form-fields']['children']
            ['city']['dataScope'] = $dataScopePrefix.'.'.'city';
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['form-fields']['children']
            ['city']['config']['customScope'] = $dataScopePrefix;
        }

        return $jsLayout;
    }
}
