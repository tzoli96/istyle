<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Model\Import;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\ProductFactory as MagentoProductFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeFrontendLabelInterface;
use Magento\Eav\Api\Data\AttributeFrontendLabelInterfaceFactory;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
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
     * @var AttributeFrontendLabelInterfaceFactory
     */
    private $attributeFrontendLabelInterfaceFactory;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var MagentoProductFactory
     */
    private $productFactory;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Product constructor.
     *
     * @param Logger                                 $logger
     * @param Config                                 $config
     * @param ProductDonor                           $productDonor
     * @param AttributeOptionLabelInterfaceFactory   $optionLabelInterfaceFactory
     * @param AttributeFactory                       $attributeFactory
     * @param ProductAttributeRepositoryInterface    $attributeRepository
     * @param ProductAttributeOption                 $productAttributeOption
     * @param Data                                   $data
     * @param OptionCollectionFactory                $optionCollectionFactory
     * @param TableFactory                           $tableFactory
     * @param AttributeFrontendLabelInterfaceFactory $attributeFrontendLabelInterfaceFactory
     * @param ProductRepositoryInterface             $productRepository
     * @param MagentoProductFactory                  $productFactory
     * @param ResourceConnection                     $resourceConnection
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
        AttributeFrontendLabelInterfaceFactory $attributeFrontendLabelInterfaceFactory,
        ProductRepositoryInterface $productRepository,
        MagentoProductFactory $productFactory,
        ResourceConnection $resourceConnection,
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
        $this->attributeFrontendLabelInterfaceFactory = $attributeFrontendLabelInterfaceFactory;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute()
    {
        $this->importAttributeValues();
    }

    /**
     * @throws NoSuchEntityException
     */
    public function executeAttribute()
    {
        $this->importAttributeLabels();
        $this->importSuperAttributes();
        $this->importSuperAttributeLinks();
    }

    /**
     * @throws NoSuchEntityException
     */
    private function importAttributeLabels()
    {
        /** @var Attribute $attributeFactory */
        $attributeFactory = $this->attributeFactory->create();
        $attributes = $attributeFactory->getCollection()->getItems();

        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            $donorStoreLabels = [];
            $attributeCode = $attribute->getAttributeCode();
            $donorLabels = $this->productDonor->getAttributeLabels($attributeCode, $this->donorStoreIds);
            foreach ($donorLabels as $donorLabel) {
                $donorStoreLabels[$this->data->getCurrentStoreId($donorLabel['store_id'])] = $donorLabel['value'];
            }

            if (!empty($donorStoreLabels)) {
                $attributeEntity = $this->attributeRepository->get($attribute->getAttributeCode());
                $frontendLabels = $attributeEntity->getStoreLabels();
                $isSet = false;
                $newLabels = [];
                foreach ($donorStoreLabels as $storeId => $storeLabel) {
                    if (((isset($frontendLabels[$storeId]) && empty($frontendLabels[$storeId]))
                            || (!isset($frontendLabels[$storeId]))) && $storeLabel != ''
                    ) {
                        $frontendLabels[$storeId] = $storeLabel;
                        $newLabels[$storeId] = $storeLabel;
                        $isSet = true;
                    }
                }

                if ($isSet) {
                    /*$attributeEntity->setStoreLabels($frontendLabels);
                    $this->attributeRepository->save($attributeEntity);*/

                    $lTable = $this->resourceConnection->getTableName('eav_attribute_label');
                    foreach ($newLabels as $storeID => $newLabel) {
                        if ($newLabel != '' && !empty($newLabel)) {
                            $this->resourceConnection->getConnection()->insert($lTable,
                                [
                                    'attribute_id' => $attribute->getId(),
                                    'store_id'     => $storeID,
                                    'value'        => $newLabel
                                ]
                            );
                        }
                    }
                }
            }
        }
    }

    private function importSuperAttributes()
    {
        $connection = $this->resourceConnection->getConnection();
        $sql = $connection->select()
            ->from('catalog_product_super_attribute')
            ->joinInner(
                ['catalog_product_entity', $connection->getTableName('catalog_product_entity')],
                'catalog_product_entity.entity_id = catalog_product_super_attribute.product_id',
                ['sku']
            )->joinInner(
                ['eav_attribute', $connection->getTableName('eav_attribute')],
                'eav_attribute.attribute_id = catalog_product_super_attribute.attribute_id',
                ['attribute_code']
            );

        $currentSuperAttributes = $connection->fetchAll($sql);
        $currentSuperAttributesBySku = [];
        foreach ($currentSuperAttributes as $currentSuperAttribute) {
            if (!isset($currentSuperAttributesBySku[$currentSuperAttribute['sku']])) {
                $currentSuperAttributesBySku[$currentSuperAttribute['sku']] = [];
            }
            $currentSuperAttributesBySku[$currentSuperAttribute['sku']][] = $currentSuperAttribute;
        }
        $donorSuperAttributes = $this->productDonor->getSuperAttributes();

        $missingDonorSuperAttributes = [];
        $missingAttributes = [];
        $missingSkus = [];
        foreach ($donorSuperAttributes as $donorSuperAttribute) {
            $isSet = false;
            if (isset($currentSuperAttributesBySku[$donorSuperAttribute['sku']])) {
                foreach ($currentSuperAttributesBySku[$donorSuperAttribute['sku']] as $currentSuperAttribute) {
                    if ($currentSuperAttribute['sku'] == $donorSuperAttribute['sku']
                        && $currentSuperAttribute['attribute_code'] == $donorSuperAttribute['attribute_code']
                    ) {
                        $isSet = true;
                        break;
                    }
                }
            }
            if (!$isSet) {
                $missingAttributes[] = $donorSuperAttribute['attribute_code'];
                $missingSkus[] = $donorSuperAttribute['sku'];
                $missingDonorSuperAttributes[] = $donorSuperAttribute;
            }
        }

        if (!empty($missingDonorSuperAttributes)) {
            $sql = $connection->select()
                ->from('catalog_product_entity')
                ->where('sku IN (?)', $missingSkus);

            $skucoll = $connection->fetchAll($sql);
            $skuId = [];
            foreach ($skucoll as $skuit) {
                $skuId[$skuit['sku']] = $skuit['entity_id'];
            }

            $sql = $connection->select()
                ->from('eav_attribute')
                ->where('attribute_code IN (?)', $missingAttributes);

            $attrcol = $connection->fetchAll($sql);
            $attrCodeId = [];
            foreach ($attrcol as $attrit) {
                $attrCodeId[$attrit['attribute_code']] = $attrit['attribute_id'];
            }
            $suATable = $this->resourceConnection->getTableName('catalog_product_super_attribute');
            $insert = 1;
            foreach ($missingDonorSuperAttributes as $missingDonorSuperAttribute) {
                if (isset($skuId[$missingDonorSuperAttribute['sku']], $attrCodeId[$missingDonorSuperAttribute['attribute_code']])) {
                    $insertArray =
                        [
                            'product_id'   => $skuId[$missingDonorSuperAttribute['sku']],
                            'attribute_id' => $attrCodeId[$missingDonorSuperAttribute['attribute_code']],
                            'position'     => $missingDonorSuperAttribute['position']
                        ];

                    $this->resourceConnection->getConnection()->insert($suATable, $insertArray);

                    $this->logger->addInfo('add super attribute - ' . $insert . ' :', $insertArray);
                    $insert++;
                } else {
                    $this->logger->addError('missing sku or attribute:',
                        [
                            $missingDonorSuperAttribute['sku'],
                            $missingDonorSuperAttribute['attribute_code']
                        ]
                    );
                }
            }
        }


    }

    private function importSuperAttributeLinks()
    {
        $connection = $this->resourceConnection->getConnection();
        $sql = $connection->select()
            ->from('catalog_product_entity');

        $productEntites = $connection->fetchAll($sql);
        $currentIdSku = [];
        $currentSkuId = [];
        foreach ($productEntites as $productEntity) {
            $currentIdSku[$productEntity['entity_id']] = $productEntity['sku'];
            $currentSkuId[$productEntity['sku']] = $productEntity['entity_id'];
        }
        $sql = $connection->select()
            ->from('catalog_product_super_link');

        $currentSuperLinks = $connection->fetchAll($sql);

        $donorIdSku = $this->productDonor->getIdSku();
        $donorSuperLinks = $this->productDonor->getSuperLinks();

        $missingDonorSuperLinks = [];
        foreach ($donorSuperLinks as $donorSuperLink) {
            $isSet = false;
            foreach ($currentSuperLinks as $currentSuperLink) {
                if (isset($currentIdSku[$currentSuperLink['parent_id']], $currentIdSku[$currentSuperLink['product_id']])) {
                    if ($currentIdSku[$currentSuperLink['parent_id']] == $donorIdSku[$donorSuperLink['parent_id']]
                        && $currentIdSku[$currentSuperLink['product_id']] == $donorIdSku[$donorSuperLink['product_id']]
                    ) {
                        $isSet = true;
                        break;
                    }
                }
            }
            if (!$isSet) {
                $missingDonorSuperLinks[] = $donorSuperLink;
            }
        }

        if (!empty($missingDonorSuperLinks)) {

            $suLTable = $this->resourceConnection->getTableName('catalog_product_super_link');
            $insert = 1;
            foreach ($missingDonorSuperLinks as $missingDonorSuperLink) {
                if (isset($currentSkuId[$donorIdSku[$missingDonorSuperLink['product_id']]], $currentSkuId[$donorIdSku[$missingDonorSuperLink['parent_id']]])) {
                    $insertArray =
                        [
                            'product_id' => $currentSkuId[$donorIdSku[$missingDonorSuperLink['product_id']]],
                            'parent_id'  => $currentSkuId[$donorIdSku[$missingDonorSuperLink['parent_id']]]
                        ];
                    $this->resourceConnection->getConnection()->insert($suLTable, $insertArray);
                    $this->logger->addInfo('add super link - ' . $insert . ' :', $insertArray);
                    $insert++;
                } else {
                    $this->logger->addError('missing sku:',
                        [
                            $donorIdSku[$missingDonorSuperLink['product_id']],
                            $donorIdSku[$missingDonorSuperLink['parent_id']]
                        ]
                    );
                }
            }
        }


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
