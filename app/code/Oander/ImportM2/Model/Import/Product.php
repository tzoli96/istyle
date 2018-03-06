<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Model\Import;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\StoreManagerInterface;
use Oander\ApiProductAttribute\Model\MethodProcessor\ProductAttributeOption;
use Oander\ImportM2\Helper\Config;
use Oander\ImportM2\Helper\Data;
use Oander\ImportM2\Logger\Logger;
use Oander\ImportM2\Model\ImportBase;
use Oander\ImportM2\Model\Resource\Donor\ProductDonor;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Oander\ApiProductAttribute\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\TableFactory;


/**
 * Class Product
 *
 * @package Oander\ImportM2\Model\Import
 */
class Product extends ImportBase
{
    private $attributeValues = [];

    /**
     * @var ProductDonor
     */
    private $productDonor;

    /**
     * @var AttributeOptionLabelInterfaceFactory
     */
    private $optionLabelInterfaceFactory;
    /**
     * @var Data
     */
    private $data;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ProductAttributeOption
     */
    private $productAttributeOption;

    /**
     * @var OptionCollectionFactory
     */
    private $optionCollectionFactory;

    /**
     * @var TableFactory
     */
    private $tableFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    private $stores = [];

    /**
     * Product constructor.
     *
     * @param Logger                               $logger
     * @param Config                               $config
     * @param ProductDonor                         $productDonor
     * @param AttributeOptionLabelInterfaceFactory $optionLabelInterfaceFactory
     * @param AttributeFactory                     $attributeFactory
     * @param ProductAttributeRepositoryInterface  $attributeRepository
     * @param ProductAttributeOption               $productAttributeOption
     * @param Data                                 $data
     * @param OptionCollectionFactory              $optionCollectionFactory
     */
    public function __construct(
        Logger $logger,
        Config $config,
        ProductDonor $productDonor,
        AttributeOptionLabelInterfaceFactory $optionLabelInterfaceFactory,
        AttributeFactory $attributeFactory,
        ProductAttributeRepositoryInterface $attributeRepository,
        ProductAttributeOption $productAttributeOption,
        Data $data,
        OptionCollectionFactory $optionCollectionFactory,
        TableFactory $tableFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($logger, $config);
        $this->attributeFactory = $attributeFactory;
        $this->attributeRepository = $attributeRepository;
        $this->productDonor = $productDonor;
        $this->attributeRepository = $attributeRepository;
        $this->optionLabelInterfaceFactory = $optionLabelInterfaceFactory;
        $this->data = $data;
        $this->productAttributeOption = $productAttributeOption;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->tableFactory = $tableFactory;
        $this->storeManager = $storeManager;
        $stores = $storeManager->getStores(true);
        foreach ($stores as $store) {
            $this->stores[$store->getId()] = $store->getCode();
        }
    }

    public function execute()
    {
        $this->importAttributeValues();
    }

    private function importAttributeValues()
    {
        $storeIds = $this->donorStoreIds;
        $storeIds[] = 0;
        $donorAttributes = $this->productDonor->getAttributes();
        foreach ($donorAttributes as $donorAttribute) {
            try {
                $attribute = $this->attributeRepository->get($donorAttribute['attribute_code']);
                $donorAttributeOptions = $this->productDonor->getAttributeOptions($donorAttribute['attribute_id'],
                    $storeIds);
                $attributeOptions = $attribute->getOptions();
                $donorAttributeOptionValues = [];
                foreach ($donorAttributeOptions as $donorAttributeOption) {
                    if (!isset($donorAttributeOptionValues[$donorAttributeOption['option_id']])) {
                        $donorAttributeOptionValues[$donorAttributeOption['option_id']] = [];
                    }
                    $donorAttributeOptionValues[$donorAttributeOption['option_id']][$donorAttributeOption['store_id']] = $donorAttributeOption['value'];
                }

                $items = ['items' => [], 'type' => 'product_attribute_option'];
                foreach ($attributeOptions as $attributeOption) {
                    foreach ($donorAttributeOptionValues as $donorAttributeOptionValue) {
                        if ($attributeOption->getLabel() == $donorAttributeOptionValue[0]) {
                            $optionId = $this->getOptionId($attribute->getAttributeCode(),
                                $attributeOption->getLabel());
                            $storeLabels = $this->getOptionStoreLabels($optionId);

                            $donorOptionLabels = [];
                            foreach ($donorAttributeOptionValue as $donorStoreId => $donorValue) {
                                if ($donorStoreId > 0) {
                                    $donorOptionLabels[$this->data->getCurrentStoreId($donorStoreId)] = $donorValue;
                                }
                            }

                            if (!empty($donorOptionLabels)) {
                                $item = [
                                    'values' => [
                                        'entity_id'      => $optionId,
                                        'frontend_label' => []
                                    ]
                                ];

                                foreach ($storeLabels as $storeId => $storeLabel) {
                                    $item['values']['frontend_label'][$this->stores[$storeId]] = $storeLabel;
                                }
                                foreach ($donorOptionLabels as $storeId => $donorValue) {
                                    $item['values']['frontend_label'][$this->stores[$storeId]] = $donorValue;
                                }

                                $items['items'][] = $item;
                                continue;
                            }
                        }
                    }
                }
                if (!empty($items['items'])) {
                    $this->productAttributeOption->execute($items);
                }

            } catch (NoSuchEntityException $e) {
                //shithappends
            }
        }
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


    /**
     * @param $attributeCode
     * @param $label
     *
     * @return int
     * @throws NoSuchEntityException
     */
    private function getOptionId($attributeCode, $label)
    {
        $this->attributeValues[$attributeCode] = [];

        /** @var Attribute $attribute */
        $attribute = $this->attributeRepository->get($attributeCode);

        /** @var \Magento\Eav\Model\Entity\Attribute\Source\Table $sourceModel */
        $sourceModel = $this->tableFactory->create();
        $sourceModel->setAttribute($attribute);

        foreach ($sourceModel->getAllOptions(false, true) as $option) {
            $this->attributeValues[$attributeCode][$option['label']] = $option['value'];
        }

        if (isset($this->attributeValues[$attributeCode][$label])) {
            return (int)$this->attributeValues[$attributeCode][$label];
        }
    }


}
