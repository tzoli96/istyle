<?php
/**
 * Oander_CustomerExtend
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\CustomerExtend\Block\System\Form\Field;

use Oander\CustomerExtend\Enum\AddressAttributeEnum;

/**
 * Class AddressAttributesOrder
 * @package Oander\IstyleCustomizatioon\Block\System\Form\Field
 */
class AddressAttributesOrder extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var null | \Magento\Framework\View\Element\BlockInterface|AddressAttributes
     */
    protected $addressAttributes = null;

    /**
     * @var null | \Oander\CustomerExtend\Block\System\Form\Field\Width
     */
    protected $width = null;

    /**
     * Returns true if the addAfter directive is set
     *
     * @return bool
     */
    public function isAddAfter()
    {
        return false;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|AddressAttributes
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAddressAttributesRenderer()
    {
        if (!$this->addressAttributes) {
            $this->addressAttributes = $this->getLayout()->createBlock(
                \Oander\CustomerExtend\Block\System\Form\Field\AddressAttributes::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->addressAttributes;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|\Oander\CustomerExtend\Block\System\Form\Field\Width
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getWidthRenderer()
    {
        if (!$this->width) {
            $this->width = $this->getLayout()->createBlock(
                \Oander\CustomerExtend\Block\System\Form\Field\Width::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->width;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            AddressAttributeEnum::COLUMN_ATTRIBUTE,
            [
                'label' => __('Attribute'),
                'renderer' => $this->getAddressAttributesRenderer()
            ]
        );
        $this->addColumn(
            AddressAttributeEnum::COLUMN_INDIVIDUAL_POSITION,
            [
                'label' => __('Individual Position'),
                'type' => 'number',
            ]
        );
        $this->addColumn(
            AddressAttributeEnum::COLUMN_COMPANY_POSITION,
            [
                'label' => __('Company Position'),
                'type' => 'number',
            ]
        );
        $this->addColumn(
            AddressAttributeEnum::COLUMN_DEFAULT_POSITION,
            [
                'label' => __('Default Position'),
                'type' => 'number',
            ]
        );
        $this->addColumn(
            AddressAttributeEnum::COLUMN_WIDTH,
            [
                'label' => __('Width'),
                'renderer' => $this->getWidthRenderer(),
            ]
        );
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(
        \Magento\Framework\DataObject $row
    ) {

        $attribute = $row->getData(AddressAttributeEnum::COLUMN_ATTRIBUTE);
        $width = $row->getData(AddressAttributeEnum::COLUMN_WIDTH);
        $options = [];
        if (is_string($attribute)) {
            $options['option_' . $this->getAddressAttributesRenderer()->calcOptionHash($attribute)] = 'selected="selected"';
        }
        if (is_string($attribute)) {
            $options['option_' . $this->getWidthRenderer()->calcOptionHash($width)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
}
