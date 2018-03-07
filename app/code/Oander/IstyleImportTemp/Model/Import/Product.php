<?php
/**
 * Oander_IstyleImportTemp
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleImportTemp\Model\Import;

use Magento\CatalogImportExport\Model\Import\Product as MagentoProduct;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Stdlib\DateTime;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Product
 *
 * @package Oander\IstyleImportTemp\Model\Import
 */
class Product extends MagentoProduct
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $storeIds = [];

    /**
     * Product constructor.
     *
     * @param StoreManagerInterface                                                        $storeManager
     * @param \Magento\Framework\Json\Helper\Data                                          $jsonHelper
     * @param \Magento\ImportExport\Helper\Data                                            $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data                        $importData
     * @param \Magento\Eav\Model\Config                                                    $config
     * @param \Magento\Framework\App\ResourceConnection                                    $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper                             $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils                                        $string
     * @param ProcessingErrorAggregatorInterface                                           $errorAggregator
     * @param \Magento\Framework\Event\ManagerInterface                                    $eventManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface                         $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface                    $stockConfiguration
     * @param \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface              $stockStateProvider
     * @param \Magento\Catalog\Helper\Data                                                 $catalogData
     * @param Import\Config                                                                $importConfig
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory
     * @param MagentoProduct\OptionFactory                                                 $optionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory      $setColFactory
     * @param MagentoProduct\Type\Factory                                                  $productTypeFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\LinkFactory                     $linkFactory
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory               $proxyProdFactory
     * @param \Magento\CatalogImportExport\Model\Import\UploaderFactory                    $uploaderFactory
     * @param \Magento\Framework\Filesystem                                                $filesystem
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory              $stockResItemFac
     * @param DateTime\TimezoneInterface                                                   $localeDate
     * @param DateTime                                                                     $dateTime
     * @param \Psr\Log\LoggerInterface                                                     $logger
     * @param \Magento\Framework\Indexer\IndexerRegistry                                   $indexerRegistry
     * @param MagentoProduct\StoreResolver                                                 $storeResolver
     * @param MagentoProduct\SkuProcessor                                                  $skuProcessor
     * @param MagentoProduct\CategoryProcessor                                             $categoryProcessor
     * @param MagentoProduct\Validator                                                     $validator
     * @param ObjectRelationProcessor                                                      $objectRelationProcessor
     * @param TransactionManagerInterface                                                  $transactionManager
     * @param MagentoProduct\TaxClassProcessor                                             $taxClassProcessor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface                           $scopeConfig
     * @param \Magento\Catalog\Model\Product\Url                                           $productUrl
     * @param array                                                                        $data
     * @param array                                                                        $dateAttrCodes
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\ImportExport\Model\Import\Config $importConfig,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory,
        \Magento\CatalogImportExport\Model\Import\Product\OptionFactory $optionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory,
        \Magento\CatalogImportExport\Model\Import\Product\Type\Factory $productTypeFactory,
        \Magento\Catalog\Model\ResourceModel\Product\LinkFactory $linkFactory,
        \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory $proxyProdFactory,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockResItemFac,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        DateTime $dateTime,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor $skuProcessor,
        \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor $categoryProcessor,
        \Magento\CatalogImportExport\Model\Import\Product\Validator $validator,
        ObjectRelationProcessor $objectRelationProcessor,
        TransactionManagerInterface $transactionManager,
        \Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor $taxClassProcessor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\Url $productUrl,
        array $data = [],
        array $dateAttrCodes = []
    ) {
        parent::__construct(
            $jsonHelper,
            $importExportData,
            $importData,
            $config,
            $resource,
            $resourceHelper,
            $string,
            $errorAggregator,
            $eventManager,
            $stockRegistry,
            $stockConfiguration,
            $stockStateProvider,
            $catalogData,
            $importConfig,
            $resourceFactory,
            $optionFactory,
            $setColFactory,
            $productTypeFactory,
            $linkFactory,
            $proxyProdFactory,
            $uploaderFactory,
            $filesystem,
            $stockResItemFac,
            $localeDate,
            $dateTime,
            $logger,
            $indexerRegistry,
            $storeResolver,
            $skuProcessor,
            $categoryProcessor,
            $validator,
            $objectRelationProcessor,
            $transactionManager,
            $taxClassProcessor,
            $scopeConfig,
            $productUrl
        );
        $this->storeManager = $storeManager;
        $stores = $this->storeManager->getStores(true);
        foreach ($stores as $store) {
            $this->storeIds[$store->getCode()] = $store->getId();
        }
    }


    /**
     * Uploading files into the "catalog/product" media folder.
     * Return a new file name if the same file is already exists.
     *
     * @param string $fileName
     *
     * @return string
     */
    protected function uploadMediaFiles($fileName, $renameFileOff = false)
    {
        try {
            $remoteFileName = $fileName;
            if (substr( $fileName, 0, 4 ) !== 'http') {
                $remoteFileName = 'http://istyle.lv/pub/media/catalog/product/' . $fileName;
            }
            $res = $this->_getUploader()->move($remoteFileName, $renameFileOff);

            return $res['file'];
        } catch (\Exception $e) {
            file_put_contents('import_missing_images.log',$remoteFileName. PHP_EOL,FILE_APPEND);
            return $fileName;
        }
    }

    /**
     * @param array $rowData
     *
     * @return array
     */
    protected function processRowCategories($rowData)
    {
        $currentStore = $this->storeManager->getStore();
        $categoriesString = empty($rowData[self::COL_CATEGORY]) ? '' : $rowData[self::COL_CATEGORY];
        $categoryIds = [];
        if (!empty($categoriesString)) {
            if (isset($this->storeIds[$rowData[self::COL_STORE_VIEW_CODE]])) {
                $categoryStoreId = $this->storeIds[$rowData[self::COL_STORE_VIEW_CODE]];
                $categoryStore = $this->storeManager->getStore($categoryStoreId);
                $this->storeManager->setCurrentStore($categoryStore);

                $categoryIds = $this->categoryProcessor->upsertCategories(
                    $categoriesString,
                    $this->getMultipleValueSeparator()
                );
            }

            $this->storeManager->setCurrentStore($currentStore);
            foreach ($this->categoryProcessor->getFailedCategories() as $error) {
                $this->errorAggregator->addError(
                    AbstractEntity::ERROR_CODE_CATEGORY_NOT_VALID,
                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL,
                    $rowData['rowNum'],
                    self::COL_CATEGORY,
                    __('Category "%1" has not been created.', $error['category'])
                    . ' ' . $error['exception']->getMessage()
                );
            }
        }

        return $categoryIds;
    }

    /**
     * Check that url_keys are not assigned to other products in DB
     *
     * @return void
     */
    protected function checkUrlKeyDuplicates()
    {
        $resource = $this->getResource();
        foreach ($this->urlKeys as $storeId => $urlKeys) {
            if ($storeId !== $this->storeIds['lv_lv']
                || $storeId !== $this->storeIds['lv_ru']
            ) {
                $storeId = $this->storeIds['lv_lv'];
            }

            $urlKeyDuplicates = $this->_connection->fetchAssoc(
                $this->_connection->select()->from(
                    ['url_rewrite' => $resource->getTable('url_rewrite')],
                    ['request_path', 'store_id']
                )->joinLeft(
                    ['cpe' => $resource->getTable('catalog_product_entity')],
                    "cpe.entity_id = url_rewrite.entity_id"
                )->where('request_path IN (?)', array_keys($urlKeys))
                    ->where('store_id IN (?)', $storeId)
                    ->where('cpe.sku not in (?)', array_values($urlKeys))
            );
            foreach ($urlKeyDuplicates as $entityData) {
                var_dump($storeId);
                var_dump($entityData['request_path']);
                $rowNum = $this->rowNumbers[$entityData['store_id']][$entityData['request_path']];
                $this->addRowError(ValidatorInterface::ERROR_DUPLICATE_URL_KEY, $rowNum);
            }
        }
    }

}
