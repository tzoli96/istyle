<?php
declare(strict_types=1);

namespace Oander\CustomerExtend\Plugin\Frontend\Magento\Checkout\Block\Checkout;
use Oander\CustomerExtend\Enum\Config as ConfigEnum;

class LayoutProcessor
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;
    /**
     * @var \Oander\AddressListAPI\Api\GetCityInterface
     */
    private $getCity;

    private $regions = null;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Oander\AddressListAPI\Api\GetCityInterface $getCity
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Oander\AddressListAPI\Api\GetCityInterface $getCity
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->getCity = $getCity;
    }

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        if($this->_scopeConfig->isSetFlag(ConfigEnum::PATH_CUSTOMER_REPLACE_POSTCODE_REGION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->regions = $this->getCity->getAllRegion();

            //shipping
            $this->callFunctionOnField($jsLayout, 'postcode', '_changeToRegion');
            $this->callFunctionOnField($jsLayout, 'postcode', '_changeToRegion', false);
            $this->callFunctionOnField($jsLayout, 'city', '_changeCityShipping');
            $this->callFunctionOnField($jsLayout, 'city', '_changeCityBilling', false);
        }
        $this->callFunctionOnField($jsLayout, 'is_company', 'changeIsCompanyShipping');
        $this->callFunctionOnField($jsLayout, 'is_company', 'changeIsCompanyBilling', false);
        $this->callFunctionOnField($jsLayout, 'is_company', '_hideField');
        $this->callFunctionOnField($jsLayout, 'is_company', '_hideField', false);
        return $jsLayout;
    }

    private function callFunctionOnField(&$jsLayout, $id, $function, $isShipping = true) {
        $field = null;
        if($isShipping) {
            if ($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"][$id]) {
                $field = &$jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"][$id];
            }
        } else {
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children'][$id])) {
                $field = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children'][$id];
            }
        }
        if($field)
            $this->$function($field);
    }

    private function _hideField(&$field) {
        $field["config"]["visible"] = 0;
    }

    private function _changeToRegion(&$postCodeElement) {
        if(count($this->regions)) {
            $options = [];
            foreach ($this->regions as $region) {
                $options[] = ["value" => $region, "label" => $region];
            }
            $postCodeElement["component"] = "Oander_IstyleCheckout/js/form/element/ui-select-postcode";
            $postCodeElement["config"]["filterOptions"] = true;
            $postCodeElement["config"]["template"] = 'ui/form/field';
            $postCodeElement["config"]["elementTmpl"] = 'oanderui/grid/filters/elements/ui-select';
            $postCodeElement["config"]["formElement"] = "select";
            $postCodeElement["config"]['options'] = $options;
            $postCodeElement["config"]["visible"] = 1;
            $postCodeElement["config"]["required"] = 1;
            $postCodeElement["config"]["multiple"] = false;
            $postCodeElement["config"]["disableLabel"] = true;
            $postCodeElement['validation']['required-entry'] = true;
            $postCodeElement["config"]['selectedPlaceholders']['defaultPlaceholder'] = $postCodeElement["placeholder"] ?? __("State/Province");
            $postCodeElement["config"]["label"] = __("State/Province");
        } else {
            $postCodeElement["config"]["label"] = __("State/Province");
        }
    }

    private function _changeCityShipping(&$cityElement) {
        if(count($this->regions)) {
            $this->_changeCity($cityElement);
            $cityElement["config"]['koSelector'] = "checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode";
        } else {
            $cityElement["config"]["label"] = __("City");
        }
    }

    private function _changeCityBilling(&$cityElement){
        if(count($this->regions)) {
            $this->_changeCity($cityElement);
            $cityElement["config"]['koSelector'] = "checkout.steps.billing-step.payment.afterMethods.billing-address-form.form-fields.postcode";
        } else {
            $cityElement["config"]["label"] = __("City");
        }
    }

    private function changeIsCompanyShipping(&$isCompanyElement){
        $isCompanyElement["config"]['customScope'] = 'shippingAddress.custom_attributes';
        $isCompanyElement['dataScope'] = 'shippingAddress.custom_attributes.is_company';
    }

    private function changeIsCompanyBilling(&$isCompanyElement){
        $isCompanyElement["config"]['customScope'] = 'billingAddressshared.custom_attributes';
        $isCompanyElement['dataScope'] = 'billingAddressshared.custom_attributes.is_company';
    }

    private function _changeCity(&$cityElement) {
        $cityElement["component"] = "Oander_Ui/js/form/element/ui-select-ajax";
        $cityElement["config"]["filterOptions"] = true;
        $cityElement["config"]["template"] = 'ui/form/field';
        $cityElement["config"]["elementTmpl"] = 'oanderui/grid/filters/elements/ui-select';
        $cityElement["config"]["formElement"] = "select";
        $cityElement["config"]['koSelector'] = "checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode";
        $cityElement["config"]['apiUrl'] = "/rest/V1/oander/addresslist/getCityByRegion/";
        $cityElement["config"]["visible"] = 1;
        $cityElement["config"]["required"] = 1;
        $cityElement["config"]["multiple"] = false;
        $cityElement["config"]["disableLabel"] = true;
        $cityElement['validation']['required-entry'] = true;
        $cityElement["config"]['selectedPlaceholders']['defaultPlaceholder'] = $cityElement["placeholder"] ?? __("City");
        $cityElement["config"]["label"] = __("City");
    }
}
