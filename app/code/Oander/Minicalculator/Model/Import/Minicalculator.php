<?php

namespace Oander\Minicalculator\Model\Import;

use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Helper\Data as HelperData;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Magento\Store\Model\StoreManagerInterface;
use Oander\DropdownProducts\Model\Import\Base\RowValidatorInterface as ValidatorInterface;
use Oander\Minicalculator\Api\Data\CalculatorInterface;

class Minicalculator extends AbstractEntity
{
    const PRODUCT_SKU = 'product_sku';
    const CALCULATOR_TYPE = 'calculator_type';
    const BAREM = 'barem';
    const INSTALLMENT = 'installment';
    const STORE_CODE = 'store_code';

    /**
     * @var string
     */
    protected $entityCode = 'minicalculator_entity';

    /**
     * List of available behaviors
     *
     * @var string[]
     */
    protected $_availableBehaviors = [
        Import::BEHAVIOR_ADD_UPDATE,
        Import::BEHAVIOR_REPLACE,
        Import::BEHAVIOR_DELETE
    ];

    protected $validColumnNames = [
        self::PRODUCT_SKU,
        self::STORE_CODE,
        self::CALCULATOR_TYPE,
        self::BAREM,
        self::INSTALLMENT,
    ];

    /**
     * @var array
     */
    protected $storeIds = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductLinkInterfaceFactory
     */
    protected $productLinkFactory;

    /**
     * DropdownProducts constructor.
     * @param JsonHelperData $jsonHelper
     * @param HelperData $importExportData
     * @param Data $importData
     * @param Config $config
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        JsonHelperData $jsonHelper,
        HelperData $importExportData,
        Data $importData,
        Config $config,
        ResourceConnection $resource,
        Helper $resourceHelper,
        StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        ProductLinkInterfaceFactory $productLinkFactory
    )
    {

        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->productLinkFactory = $productLinkFactory;
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum): bool
    {
        $this->_validatedRows[$rowNum] = true;

        $hasError = false;
        foreach ($this->validColumnNames as $columnName) {
            if (!array_key_exists($columnName, $rowData)) {
                $this->addRowError(str_replace('%s', $columnName, ValidatorInterface::ERROR_MISSING), $rowNum);
                return false;
            }
        }

        if (empty($rowData[self::STORE_CODE])) {
            $this->addRowError(str_replace('%s', self::STORE_CODE, ValidatorInterface::ERROR_EMPTY), $rowNum);
            $hasError = true;
        }

        if (empty($rowData[self::PRODUCT_SKU])) {
            $this->addRowError(str_replace('%s', self::PRODUCT_SKU, ValidatorInterface::ERROR_EMPTY), $rowNum);
            $hasError = true;
        }

        if (!array_key_exists($rowData[self::STORE_CODE], $this->getStoreIds())) {
            $this->addRowError(str_replace('%s', $rowData[self::STORE_CODE], ValidatorInterface::ERROR_EMPTY), $rowNum);
            $hasError = true;
        }

        try {
            $this->productRepository->get($rowData[self::PRODUCT_SKU]);
        } catch (NoSuchEntityException $e) {
            $this->addRowError(str_replace('%s', $rowData[self::PRODUCT_SKU], ValidatorInterface::ERROR_PRODUCT_NOT_EXIST), $rowNum);
            $hasError = true;
        }

        if ($hasError) {
            return false;
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Import data rows.
     *
     * @return boolean
     */
    protected function _importData()
    {
        $behavior = $this->getBehavior();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $product = $this->productRepository->get($rowData[self::PRODUCT_SKU], true, $this->getStoreId($rowData[self::STORE_CODE]));
                if ($behavior === Import::BEHAVIOR_ADD_UPDATE || $behavior === Import::BEHAVIOR_REPLACE || $behavior === Import::BEHAVIOR_APPEND) {
                    if (empty($rowData[self::CALCULATOR_TYPE])) {
                        continue;
                    }
                    $product->setCustomAttribute(CalculatorInterface::CALCULATOR_TYPE, $rowData[self::CALCULATOR_TYPE]);
                    $product->setCustomAttribute(CalculatorInterface::CALCULATOR_BAREM, $rowData[self::BAREM]);
                    $product->setCustomAttribute(CalculatorInterface::CALCULATOR_INSTALLMENT, $rowData[self::INSTALLMENT]);
                } else {
                    $product->setCustomAttribute(CalculatorInterface::CALCULATOR_TYPE, null);
                    $product->setCustomAttribute(CalculatorInterface::CALCULATOR_BAREM, null);
                    $product->setCustomAttribute(CalculatorInterface::CALCULATOR_INSTALLMENT, null);
                }
                $product->save();
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getEntityTypeCode(): string
    {
        return $this->entityCode;
    }

    /**
     * @param $storeCode
     * @return mixed
     */
    public function getStoreId($storeCode)
    {
        $storeIds = $this->getStoreIds();
        return $storeIds[$storeCode];
    }

    /**
     * @return mixed
     */
    protected function getStoreIds()
    {
        if (empty($this->storeIds)) {
            foreach ($this->storeManager->getStores(true) as $store) {
                $this->storeIds[$store->getCode()] = $store->getId();
            }
        }

        return $this->storeIds;

    }

    /**
     *
     * Multiple value separator getter.
     * @return string
     */
    public function getMultipleValueSeparator()
    {
        if (!empty($this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR])) {
            return $this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR];
        }
        return Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR;
    }
}