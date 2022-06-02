<?php

declare(strict_types=1);

namespace Oander\AddressFieldsProperties\Plugin\Frontend\Magento\Checkout\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor as OrigLayoutProcessor;
use Oander\AddressFieldsProperties\Helper\Config as ConfigHelper;

/**
 * Manipulating checkout fields with validation and formatting
 */
class LayoutProcessor
{
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * LayoutProcessor constructor.
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper
    )
    {
        $this->configHelper = $configHelper;
    }

    /**
     * Manipulate LayoutProcessor with fields formating, placeholder and validations
     * @param OrigLayoutProcessor $subject
     * @param $result
     * @return mixed
     */
    public function afterProcess(
        OrigLayoutProcessor $subject,
        array $result
    ) {
        if(
            isset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']) &&
            is_array($result['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'])
        ) {
            foreach ($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] as $key => &$payment) {
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

    /**
     * Add Formating and Validations and Placeholder to field
     * @param string $attributeCode
     * @param array $field
     * @return void
     */
    private function addProperties($attributeCode, &$field)
    {
        if(isset($field["children"]) && is_array($field["children"]))
        {
            foreach ($field["children"] as &$childfield)
            {
                $this->addProperties($attributeCode, $childfield);
            }
        } else {
            $field['placeholder'] = $this->addPlaceHolder($attributeCode, $field['placeholder'] ?? "");
            $field['additionalClasses'] = $this->addFormattingClasses($attributeCode, $field['additionalClasses'] ?? "");
            $field['validation'] = $this->addValidations($attributeCode, $field['validation'] ?? []);

            //Do not fill additionalclasses if empty
            if (empty($field['additionalClasses'])) {
                unset($field['additionalClasses']);
            }
        }
    }

    /**
     * Add Formating to field
     * @param string $attributeCode
     * @param string $origClasses
     * @return string
     */
    private function addFormattingClasses($attributeCode, $origClasses = "")
    {
        $classes = $this->configHelper->getFormattingClasses($attributeCode, true);
        $classes[] = $origClasses;
        return implode(" ", $classes);
    }

    /**
     * Add Validations to field
     * @param string $attributeCode
     * @param string $origValidations
     * @return array
     */
    private function addValidations($attributeCode, $origValidations = [])
    {
        $classes = $this->configHelper->getValidations($attributeCode);
        $classes = array_merge($origValidations, $classes);
        return $classes;
    }

    /**
     * Add Placeholder to field
     * @param string $attributeCode
     * @param string $origPlaceholder
     * @return string
     */
    private function addPlaceHolder($attributeCode, $origPlaceholder = "")
    {
        $placeholder = $this->configHelper->getPlaceholder($attributeCode);
        return !empty($placeholder)?$placeholder:$origPlaceholder;
    }
}
