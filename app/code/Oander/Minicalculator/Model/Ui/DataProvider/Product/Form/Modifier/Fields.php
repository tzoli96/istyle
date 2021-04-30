<?php

namespace Oander\Minicalculator\Model\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Oander\Minicalculator\Api\Data\CalculatorInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\MultiSelect;

class Fields extends AbstractModifier
{
    // Components indexes
    const MINICALCULATOR_FIELDSET_INDEX = 'minicalculator_fieldset';
    const CUSTOM_FIELDSET_CONTENT = 'custom_fieldset_content';
    const CONTAINER_HEADER_NAME = 'custom_fieldset_content_header';

    // Fields names
    const FIELD_NAME_TEXT = 'example_text_field';
    const FIELD_CALCULATOR_TYPE = 'calculator_type_field';
    const FIELD_CALCULATOR_BAREM = 'calculator_barem_field';
    const FIELD_CALCULATOR_INSTALLMENT = 'calculator_installment_field';

    private $locator;
    private $meta;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
        $this->locator = $locator;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        //$this->addCustomFieldset();
        $this->clearFieldset();
        return $this->meta;

    }

    /**
     * @return void
     */
    private function clearFieldset()
    {
        $this->meta['minicalculator']['children']['container_calculator_type']['children']['calculator_type']['arguments']['data']['config']['component']='Oander_Minicalculator/js/form/element/calculator_type';
        $this->meta['minicalculator']['children']['container_calculator_type']['children']['calculator_type']['arguments']['data']['config']['caption']='Please select...';
        $this->meta['minicalculator']['children']['container_calculator_barem']['children']['calculator_barem']['arguments']['data']['config']['component']='Oander_Minicalculator/js/form/element/calculator_barem';
        $this->meta['minicalculator']['children']['container_calculator_barem']['children']['calculator_barem']['arguments']['data']['config']['caption']='Please select...';
        $this->meta['minicalculator']['children']['container_calculator_installment']['children']['calculator_installment']['arguments']['data']['config']['component']='Oander_Minicalculator/js/form/element/calculator_installment';
        $this->meta['minicalculator']['children']['container_calculator_installment']['children']['calculator_installment']['arguments']['data']['config']['caption']='Please select...';
    }

    /**
     * Merge existing meta-data with our meta-data (do not overwrite it!)
     *
     * @return void
     */
    protected function addCustomFieldset()
    {
        $this->meta = array_merge_recursive(
            $this->meta,
            [
                static::MINICALCULATOR_FIELDSET_INDEX => $this->getFieldsetConfig(),
            ]
        );
    }

    /**
     * Declare ours fieldset config
     *
     * @return array
     */
    protected function getFieldsetConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Minicalculator'),
                        'componentType' => Fieldset::NAME,
                        'dataScope' => static::DATA_SCOPE_PRODUCT,
                        'provider' => static::DATA_SCOPE_PRODUCT . '_data_source',
                        'ns' => static::FORM_NAME,
                        'collapsible' => true,
                        'sortOrder' => 10,
                        'opened' => true,
                    ],
                ],
            ],
            'children' => [
                static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                static::FIELD_CALCULATOR_TYPE => $this->getCalculatorTypeField(20),
                static::FIELD_CALCULATOR_BAREM => $this->getCalculatorBaremField(30),
                static::FIELD_CALCULATOR_INSTALLMENT => $this->getCalculatorInstallmentField(40),
            ],
        ];
    }

    /**
     * Get config for header container
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getHeaderContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $sortOrder,

                    ],
                ],
            ],
            'children' => [],
        ];
    }

    /**
     * Example select field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getCalculatorTypeField($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Calculator Type'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => static::FIELD_CALCULATOR_TYPE,
                        'component' => 'Oander_Minicalculator/js/form/element/calculator_type',
                        'caption' => 'Please select...',
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'visible' => true,
                        'disabled' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Example select field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getCalculatorBaremField($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Calculator Barem'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => static::FIELD_CALCULATOR_BAREM,
                        'dataType' => Text::NAME,
                        'component' => 'Oander_Minicalculator/js/form/element/calculator_barem',
                        'caption' => 'Please select...',
                        'sortOrder' => $sortOrder,
                        'visible' => true,
                        'disabled' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     *
     * @param $sortOrder
     * @return array
     */
    protected function getCalculatorInstallmentField($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Calculator Installment'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => static::FIELD_CALCULATOR_INSTALLMENT,
                        'component' => 'Oander_Minicalculator/js/form/element/calculator_installment',
                        'caption' => 'Please select...',
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'visible' => true,
                        'disabled' => false,
                    ],
                ],
            ],
        ];
    }
}
