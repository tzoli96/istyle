<?php
/**
 * Oander_IstyleImportTemp
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */
namespace Oander\IstyleImportTemp\Model\Import\Product\Type;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\ConfigurableImportExport\Model\Import\Product\Type\Configurable as MagentoConfigurable;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Oander\ApiProductAttribute\Model\MethodProcessor\ProductAttributeOption;
use Oander\ImportM2\Helper\Config;
use Oander\ImportM2\Helper\Data;
use Oander\ImportM2\Logger\Logger;
use Oander\ImportM2\Model\ImportBase;
use Oander\ImportM2\Model\Resource\Donor\ProductDonor;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Oander\ApiProductAttribute\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\TableFactory;

/**
 * Class Configurable
 *
 * @package Oander\IstyleImportTemp\Model\Import\Product\Type
 */
class Configurable extends MagentoConfigurable
{
    private $attributeValues = [];
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var TableFactory
     */
    private $tableFactory;
    /**
     * @var OptionCollectionFactory
     */
    private $optionCollectionFactory;
    /**
     * @var ProductAttributeOption
     */
    private $productAttributeOption;

    /**
     * Configurable constructor.
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory  $attrSetColFac
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param \Magento\Framework\App\ResourceConnection                                $resource
     * @param array                                                                    $params
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface                      $productTypesConfig
     * @param \Magento\ImportExport\Model\ResourceModel\Helper                         $resourceHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory           $_productColFac
     * @param StoreManagerInterface                                                    $storeManager
     * @param ProductAttributeRepositoryInterface                                      $attributeRepository
     * @param ProductAttributeOption                                                   $productAttributeOption
     * @param Data                                                                     $data
     * @param OptionCollectionFactory                                                  $optionCollectionFactory
     * @param TableFactory                                                             $tableFactory
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac,
        \Magento\Framework\App\ResourceConnection $resource,
        array $params,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypesConfig,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $_productColFac,
        StoreManagerInterface $storeManager,
        ProductAttributeRepositoryInterface $attributeRepository,
        ProductAttributeOption $productAttributeOption,
        Data $data,
        OptionCollectionFactory $optionCollectionFactory,
        TableFactory $tableFactory
    ) {
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params, $productTypesConfig, $resourceHelper,
            $_productColFac);
        $this->attributeRepository = $attributeRepository;
        $this->storeManager = $storeManager;
        $this->tableFactory = $tableFactory;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->productAttributeOption = $productAttributeOption;
    }

    /**
     * Prepare attributes values for save: exclude non-existent, static or with empty values attributes;
     * set default values if needed
     *
     * @param array $rowData
     * @param bool $withDefaultValue
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepareAttributesWithDefaultValueForSave(array $rowData, $withDefaultValue = true)
    {
        $resultAttrs = [];

        foreach ($this->_getProductAttributes($rowData) as $attrCode => $attrParams) {
            if ($attrParams['is_static']) {
                continue;
            }
            if (isset($rowData[$attrCode]) && strlen($rowData[$attrCode])) {
                if (in_array($attrParams['type'], ['select', 'boolean'])) {
                    if ($rowData[$attrCode][0] === '"' && substr($rowData[$attrCode], -1) === '"') {
                        $rowData[$attrCode] = str_split($rowData[$attrCode]);
                        unset($rowData[$attrCode][count($rowData[$attrCode]) - 1], $rowData[$attrCode][0]);
                        $rowData[$attrCode] = implode('', $rowData[$attrCode]);

                        if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                            && strpos($rowData[$attrCode], '""') !== false) {
                            $rowData[$attrCode] = str_replace('""', '"', $rowData[$attrCode]);
                        }
                    } elseif (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                        && in_array($rowData[$attrCode], $attrParams['options'])) {
                        $rowData[$attrCode] = array_search($rowData[$attrCode], $attrParams['options']);
                    } elseif ($attrCode === 'msrp_display_actual_price_type') {
                        $rowData[$attrCode] = (int)$rowData[$attrCode] - 1;
                        $rowData[$attrCode] = array_search($rowData[$attrCode], $attrParams['options']);
                    }

                    if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                        && strpos($rowData[$attrCode], '"') !== false) {
                        $rowData[$attrCode] = str_replace('"', '&quot;', $rowData[$attrCode]);
                    }

                    if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                        && strpos($rowData[$attrCode], '&quot;') !== false) {
                        $rowData[$attrCode] = str_replace('&quot;', '', $rowData[$attrCode]);
                    }

                    if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                        && $attrCode === 'config_size' && $rowData[$attrCode] === '0-5 m') {
                        $rowData[$attrCode] = '0,5 m';
                    }

                    if (isset($attrParams['options'][strtolower($rowData[$attrCode])])) {
                        $resultAttrs[$attrCode] = $attrParams['options'][strtolower($rowData[$attrCode])];
                    }

                } elseif ('multiselect' == $attrParams['type']) {
                    $resultAttrs[$attrCode] = [];
                    foreach ($this->_entityModel->parseMultiselectValues($rowData[$attrCode]) as $value) {

                        if ($value[0] === '"' && substr($value, -1) === '"') {
                            $value = str_split($value);
                            unset($value[count($value) - 1], $value[0]);
                            $value = implode('', $value);

                            if (!isset($attrParams['options'][strtolower($value)])
                                && strpos($value, '""') !== false) {
                                $value = str_replace('""', '"', $value);
                            }
                        } elseif (!isset($attrParams['options'][strtolower($value)])
                            && in_array($value, $attrParams['options'])) {
                            $value = array_search($value, $attrParams['options']);
                        } elseif ($attrCode === 'msrp_display_actual_price_type') {
                            $rowData[$attrCode] = (int)$rowData[$attrCode] - 1;
                            $rowData[$attrCode] = array_search($rowData[$attrCode], $attrParams['options']);
                        }

                        if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                            && strpos($rowData[$attrCode], '"') !== false) {
                            $rowData[$attrCode] = str_replace('"', '&quot;', $rowData[$attrCode]);
                        }

                        if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                            && strpos($rowData[$attrCode], '&quot;') !== false) {
                            $rowData[$attrCode] = str_replace('&quot;', '', $rowData[$attrCode]);
                        }

                        if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                            && $attrCode === 'config_size' && $rowData[$attrCode] === '0-5 m') {
                            $rowData[$attrCode] = '0,5 m';
                        }

                        if (isset($attrParams['options'][strtolower($rowData[$attrCode])])) {
                            $resultAttrs[$attrCode][] = $attrParams['options'][strtolower($value)];
                        }

                    }
                    $resultAttrs[$attrCode] = implode(',', $resultAttrs[$attrCode]);
                } else {
                    $resultAttrs[$attrCode] = $rowData[$attrCode];
                }
            } elseif (array_key_exists($attrCode, $rowData)) {
                $resultAttrs[$attrCode] = $rowData[$attrCode];
            } elseif ($withDefaultValue && null !== $attrParams['default_value']) {
                $resultAttrs[$attrCode] = $attrParams['default_value'];
            }
        }

        return $resultAttrs;
    }

    /**
     * Validate particular attributes columns.
     *
     * @param array $rowData
     * @param int   $rowNum
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _isParticularAttributesValid(array $rowData, $rowNum)
    {
        if (!empty($rowData['_super_attribute_code'])) {
            $superAttrCode = $rowData['_super_attribute_code'];

            if ($rowData['_super_attribute_option'] == '0-5 m' && $superAttrCode == 'config_size') {
                $rowData['_super_attribute_option'] = '0,5 m';
            }

            if (!$this->_isAttributeSuper($superAttrCode)
                || (isset($rowData['_super_attribute_option']) && strlen($rowData['_super_attribute_option']))
            ) {
                $options = $this->_superAttributes[$superAttrCode]['options'];
                if (!empty($options)) {
                    $done = false;
                    foreach ($options as $optionLabel => $optionId) {
                        $storeLabels = $this->getOptionStoreLabels($optionId);
                        if (!empty($storeLabels)) {
                            foreach ($storeLabels as $storeLabel) {
                                if (strtolower($storeLabel) == strtolower($rowData['_super_attribute_option'])) {
                                    $rowData['_super_attribute_option'] = $optionLabel;
                                    $done = true;
                                    break;
                                }
                            }
                        }
                        if ($done) {
                            break;
                        }
                    }
                }
            }

            if (!$this->_isAttributeSuper($superAttrCode)) {
                // check attribute superity
                $this->_entityModel->addRowError(self::ERROR_ATTRIBUTE_CODE_IS_NOT_SUPER, $rowNum);
                return false;
            } elseif (isset($rowData['_super_attribute_option']) && strlen($rowData['_super_attribute_option'])) {
                $optionKey = strtolower($rowData['_super_attribute_option']);
                if (!isset($this->_superAttributes[$superAttrCode]['options'][$optionKey])) {
                    $this->_entityModel->addRowError(self::ERROR_INVALID_OPTION_VALUE, $rowNum);
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param $optionId
     *
     * @return array
     */
    private function getOptionStoreLabels($optionId)
    {
        $storeLabels = [];
        $optionCollection = $this->optionCollectionFactory->create()
            ->setIdFilter($optionId)
            ->setStoreFilter(null, false);

        foreach ($optionCollection->getData() as $option) {
            $storeLabels[$option['store_id']] = $option['value'];
        }

        return $storeLabels;
    }
}
