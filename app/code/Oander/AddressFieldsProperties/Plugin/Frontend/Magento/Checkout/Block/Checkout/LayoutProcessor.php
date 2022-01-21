<?php

declare(strict_types=1);

namespace Oander\AddressFieldsProperties\Plugin\Frontend\Magento\Checkout\Block\Checkout;

class LayoutProcessor
{
    /**
     * @var \Oander\AddressFieldsProperties\Helper\Config
     */
    private $configHelper;

    /**
     * LayoutProcessor constructor.
     * @param \Oander\AddressFieldsProperties\Helper\Config $configHelper
     */
    public function __construct(
        \Oander\AddressFieldsProperties\Helper\Config $configHelper
    )
    {
        $this->configHelper = $configHelper;
    }

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result
    ) {
        if(
            isset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']) &&
            is_array($result['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'])
        ) {
            foreach ($result['components']['checkout']['children']['steps']['children']['billing-step']['children']
                     ['payment']['children']['payments-list']['children'] as $key => &$payment) {
                if (isset($payment['children']['form-fields']['children']) && is_array($payment['children']['form-fields']['children'])) {
                    foreach ($payment['children']['form-fields']['children'] as $attributeCode => &$field) {
                        $this->addProperties($attributeCode, $field);
                    }
                }
            }
        }

        /** New checkout compatibility */
        //SHIPPING
        if(
            isset($result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']) &&
            is_array($result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])
        )
        {
            foreach ($result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] as $attributeCode => &$field)
            {
                $this->addProperties($attributeCode, $field);
            }
        }
        //BILLING
        if(
            isset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['form-fields']['children']) &&
            is_array($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children'])
        )
        {
            foreach ($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
                ['children']['form-fields']['children'] as $attributeCode => &$field)
            {
                $this->addProperties($attributeCode, $field);
            }
        }
        return $result;
    }

    private function addProperties($attributeCode, &$field)
    {
        $field['placeholder'] = $this->addPlaceHolder($attributeCode, $field['placeholder']??"");
        $field['additionalClasses'] = $this->addFormattingClasses($attributeCode, $field['additionalClasses']??"");
        $field['validation'] = $this->addValidations($attributeCode, $field['validation']??[]);
        
        //Do not fill additionalclasses if empty
        if(empty($field['additionalClasses']))
        {
            unset($field['additionalClasses']);
        }
    }

    private function addFormattingClasses($attributeCode, $origClasses = "")
    {
        $classes = $this->configHelper->getFormattingClasses($attributeCode);
        $classes[] = $origClasses;
        return implode(" ", $classes);
    }

    private function addValidations($attributeCode, $origValidations = [])
    {
        $classes = $this->configHelper->getValidations($attributeCode);
        $classes = array_merge($origValidations, $classes);
        return $classes;
    }

    private function addPlaceHolder($attributeCode, $origPlaceholder = "")
    {
        $placeholder = $this->configHelper->getPlaceholder($attributeCode);
        return !empty($placeholder)?$placeholder:$origPlaceholder;
    }
}
