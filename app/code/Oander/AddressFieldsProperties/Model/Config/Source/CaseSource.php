<?php
/**
 * Used in creating options for Case config value selection
 */
namespace Oander\AddressFieldsProperties\Model\Config\Source;

class CaseSource implements \Magento\Framework\Option\ArrayInterface
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
            \Oander\AddressFieldsProperties\Enum\CaseEnum::CASE_NOCHANGE => __('No Change'),
            \Oander\AddressFieldsProperties\Enum\CaseEnum::CASE_LOWERCASE => __('Lowercase Only'),
            \Oander\AddressFieldsProperties\Enum\CaseEnum::CASE_UPPERCASE => __('Uppercase Only')
        ];
    }
}
