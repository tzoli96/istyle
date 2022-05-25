<?php

namespace Oander\RaiffeisenPayment\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class EligibilityQuestions extends AbstractFieldArray
{

    /**
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'question',
            [
                'label' => __('Question'),
                'class' => 'required-entry',
            ]
        );
        $this->addColumn(
            'valid_answer',
            [
                'label' => __('Valid answer'),
                'class' => 'required-entry',
            ]
        );
        $this->addColumn(
            'invalid_answer',
            [
                'label' => __('Invalid answer'),
                'class' => 'required-entry',
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
    /**
     * @param DataObject $row
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];
        $dropdownField = $row->getDropdownField();
        if ($dropdownField !== null)
        {
            $options['option_' . $this->getDropdownRenderer()->calcOptionHash($dropdownField)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}