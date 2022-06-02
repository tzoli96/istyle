<?php

namespace Oander\AddressFieldsProperties\Plugin\Frontend\Magento\Customer\Helper;

use Magento\Customer\Helper\Address as OrigAddress;
use Oander\AddressFieldsProperties\Helper\Config as ConfigHelper;

/**
 * Class Address. Plugin to add classes for address fields in profile address edit.
 */
class Address
{
    const ADDRESSATTRIBUTE_CLASS = "oanderaddressattribute";
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * Address constructor.
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper
    )
    {
        $this->configHelper = $configHelper;
    }

    /**
     * @param OrigAddress $subject
     * @param \Closure $proceed
     * @param string $attributeCode
     * @return mixed|string
     */
    public function aroundGetAttributeValidationClass(
        OrigAddress $subject,
        \Closure      $proceed,
                      $attributeCode
    ) {
        $result = $proceed($attributeCode);
        $formatsAndValidations = $this->configHelper->getFormattingClasses($attributeCode, true);
        $formatsAndValidations[] = self::ADDRESSATTRIBUTE_CLASS;
        $formatsAndValidations[] = self::ADDRESSATTRIBUTE_CLASS . "-" . $attributeCode;
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