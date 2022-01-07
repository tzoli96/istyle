<?php

/**
 * Used in creating options for Validation config value selection
 *
 */
namespace Oander\AddressFieldsProperties\Model\Config\Source;

class ValidationType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->toArray() as $value => $label)
        {
            $result[] = ["value" => $value, "label" => $label];
        }
        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            \Oander\AddressFieldsProperties\Enum\ValidationType::VALIDATIONTYPE_OFF => __('No'),
            \Oander\AddressFieldsProperties\Enum\ValidationType::VALIDATIONTYPE_STRINGLENGTH => __('String Length'),
            \Oander\AddressFieldsProperties\Enum\ValidationType::VALIDATIONTYPE_FULLREGEX => __('Full Regex')
        ];
    }
}