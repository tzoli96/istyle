<?php
declare(strict_types=1);

namespace Oander\CustomerExtend\Plugin\Frontend\Magento\Checkout\Block\Checkout;

class LayoutProcessor
{

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        if(
            isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']['postcode'])
        ) {
            $this->_changeToRegion($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']['postcode']);
        }
        if($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]["postcode"]) {
            $this->_changeToRegion($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]["postcode"]);
        }
        return $jsLayout;
    }

    private function _changeToRegion(&$postCodeElement) {
        $postCodeElement["component"] = "Magento_Ui/js/form/element/ui-select";
        $postCodeElement["config"]["filterOptions"] = true;
        $postCodeElement["config"]["template"] = 'ui/form/field';
        $postCodeElement["config"]["elementTmpl"] = 'ui/grid/filters/elements/ui-select';
        $postCodeElement["config"]["formElement"] = "select";
        $postCodeElement["config"]['options'] = [["value"=>"Egy","label"=>"Egy"],["value" => "Száztizenhárom", "label" => "Száztizenhárom"],["value" => "Négymillió", "label" => "Négymillió"], ["value" => "ABC", "label" => "ABC"], ["value" => "Sanyi", "label" => "Sanyi"]];
        $postCodeElement["config"]["visible"] = 1;
        $postCodeElement["config"]["required"] = 1;
        $postCodeElement["config"]["multiple"] = false;
        $postCodeElement["config"]["disableLabel"] = true;
        $postCodeElement['validation']['required-entry'] = true;
        $postCodeElement["config"]['selectedPlaceholders']['defaultPlaceholder'] = $postCodeElement["placeholder"] ?? __("Region");
    }
}
