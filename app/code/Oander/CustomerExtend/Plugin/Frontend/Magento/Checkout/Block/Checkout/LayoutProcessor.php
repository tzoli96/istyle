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
            if(
                isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children']) &&
                is_array($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'])
            ) {
                foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] as $key => &$payment) {
                    if (isset($payment['children']['form-fields']['children']["postcode"])) {
                        $this->_changeToRegion($payment['children']['form-fields']['children']["postcode"]);
                    }
                }
            }

            //shipping
            if ($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]["postcode"]) {
                $this->_changeToRegion($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]["postcode"]);
            }

            //billing
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['postcode'])) {
                $this->_changeToRegion($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children']['postcode']);
            }
        }
        return $jsLayout;
    }

    private function _changeToRegion(&$postCodeElement) {
        if(count($this->regions)) {
            $options = [];
            foreach ($this->regions as $region) {
                $options[] = ["value" => $region, "label" => $region];
            }
            $postCodeElement["component"] = "Magento_Ui/js/form/element/ui-select";
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
}
