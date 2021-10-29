<?php

namespace Oney\ThreeByFour\Block\Adminhtml\Menu\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class PaymentMethods extends AbstractFieldArray
{
    protected $typeOptions;
    protected $cmsOptions;

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'method',
            [
                'label' => __('Method'),
                'size' => '100px'
            ]
        );
        $this->addColumn(
            'activated',
            [
                'label' => __('Active'),
                'size' => '100px'
            ]
        );

    }
}
