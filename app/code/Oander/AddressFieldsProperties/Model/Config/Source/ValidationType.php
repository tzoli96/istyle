<?php
/**
 * Used in creating options for Validation config value selection
 */
namespace Oander\AddressFieldsProperties\Model\Config\Source;

use Oander\AddressFieldsProperties\Enum\ValidationType as ValidationTypeEnum;

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
            ValidationTypeEnum::VALIDATIONTYPE_OFF => __('No'),
            ValidationTypeEnum::VALIDATIONTYPE_STRINGLENGTH => __('String Length'),
            ValidationTypeEnum::VALIDATIONTYPE_FULLREGEX => __('Full Regex')
        ];
    }
}
