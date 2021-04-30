<?php

namespace Oander\Minicalculator\Model\Import\Behavior;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Source\Import\Behavior\Custom as OriginalCustom;

class Custom extends OriginalCustom
{
    /**
     * Prepare and return array of option values
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->toArray();
        if (is_array($options) && count($options) > 0) {
            foreach ($options as $value => $label) {
                $optionArray[] = ['label' => $label, 'value' => $value];
            }
        }
        return $optionArray;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            Import::BEHAVIOR_ADD_UPDATE => __('Add/Update'),
            Import::BEHAVIOR_REPLACE => __('Replace'),
            Import::BEHAVIOR_DELETE => __('Delete')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'minicalculator_entity';
    }
}