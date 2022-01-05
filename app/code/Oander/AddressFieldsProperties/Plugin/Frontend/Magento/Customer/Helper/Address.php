<?php

namespace Oander\AddressFieldsProperties\Plugin\Frontend\Magento\Customer\Helper;

/**
 * Class Address. Plugin to add classes for address fields in profile address edit.
 * @package Oander\SimpleVATNumberValidation\Plugin\Frontend\Magento\Customer\Helper
 */
class Address
{
    /**
     * @var \Oander\AddressFieldsProperties\Helper\Config
     */
    private $configHelper;

    /**
     * Address constructor.
     * @param \Oander\AddressFieldsProperties\Helper\Config $configHelper
     */
    public function __construct(
        \Oander\AddressFieldsProperties\Helper\Config $configHelper
    )
    {
        $this->configHelper = $configHelper;
    }

    /**
     * @param \Magento\Customer\Helper\Address $subject
     * @param \Closure $proceed
     * @param string $attributeCode
     * @return mixed|string
     */
    public function aroundGetAttributeValidationClass(
        \Magento\Customer\Helper\Address $subject,
        \Closure $proceed,
        $attributeCode
    ) {
        $result = $proceed($attributeCode);
        $formatsAndValidations = $this->configHelper->getFormattingClasses($attributeCode, true);
        if($this->configHelper->getPlaceholder($attributeCode))
        {
            $formatsAndValidations[] = "oanderplaceholder";
            $formatsAndValidations[] = "oanderplaceholder-" . $attributeCode;
        }
        if(!empty($formatsAndValidations))
        {
            if ($result == "")
                $result = implode($formatsAndValidations, " ");
            else
                $result .= " " . implode($formatsAndValidations, " ");
        }
            return $result;
    }
}