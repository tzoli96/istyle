<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Block\System\Form\Field;

use Oander\IstyleCustomization\Enum\AddressAttributeEnum;

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
                \Oander\IstyleCustomization\Block\System\Form\Field\AddressAttributes::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->addressAttributes;
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
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(
        \Magento\Framework\DataObject $row
    ) {

        $attribute = $row->getData('attribute');
        $options = [];
        if (is_string($attribute)) {
            $options['option_' . $this->getAddressAttributesRenderer()->calcOptionHash($attribute)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
}
