<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\AddressListAPI\Model\Adminhtml\Config\Backend;

use Magento\Config\Model\Config\Backend\File;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManager;
use Magento\Framework\App\CacheInterface;

/**
 * Class StateCityCsv
 * @package Oander\FanCourierValidator\Model\Adminhtml\Config\Backend
 */
class StateCityCsv extends File
{
    const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|null
     */
    private $resourceConnection;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param File\RequestData\RequestDataInterface $requestData
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\File\Csv $csv
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        File\RequestData\RequestDataInterface $requestData,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\File\Csv $csv,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        Filesystem $filesystem,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $config, $cacheTypeList, $uploaderFactory, $requestData, $filesystem, $resource, $resourceCollection, $data);
        $this->messageManager = $messageManager;
        $this->csv = $csv;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedExtensions()
    {
        return ['csv'];
    }

    /**
     * @return StateCityCsv
     */
    public function beforeSave()
    {
        $connection = $this->resourceConnection->getConnection();
        $countryCode = $this->_config->getValue(
            self::COUNTRY_CODE_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $file = $this->getFileData();
        if (!isset($file['tmp_name'])) {
            $value = $this->getValue();
            if (isset($value['delete']) && $value['delete'] == 1) {
                $connection->delete(
                    $connection->getTableName(\Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::TABLE),
                    $connection->quoteInto(\Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::REGION . ' = ?', $countryCode)
                );
                $this->messageManager->addSuccessMessage(__('Cities removed successfully'));
            }

            return parent::beforeSave();
        }

        $csvData = $this->csv->getData($file['tmp_name']);
        $stateIndex = 0;
        $cityIndex = 1;
        foreach ($csvData[0] as $key => $csvHeader) {
            switch ($csvHeader) {
                case 'Region':
                    $stateIndex = $key;
                    break;
                case 'City':
                    $cityIndex = $key;
                    break;
            }
        }
        unset($csvData[0]);

        $stateCityPairs = [];
        foreach ($csvData as $entityId => $csvRow) {
            $stateCityPairs[] = [
                \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::COUNTRY_CODE => $countryCode,
                \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::REGION => $csvRow[$stateIndex],
                \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::CITY => $csvRow[$cityIndex]
            ];
        }

        if(count($stateCityPairs))
            $this->resourceConnection->getConnection()->insertMultiple(\Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::TABLE, $stateCityPairs);

        $this->messageManager->addSuccessMessage(__('%1 cities added successfully', count($stateCityPairs)));

        parent::beforeSave();
    }

    /**
     * @return StateCityCsv
     */
    public function afterDelete()
    {
        $this->cityRepository->truncate();
        $this->messageManager->addSuccessMessage(__('Cities truncated successfully'));

        $this->stateRepository->truncate();
        $this->messageManager->addSuccessMessage(__('States truncated successfully'));

        $this->stateCityRepository->truncate();
        $this->messageManager->addSuccessMessage(__('State-city pairs truncated successfully'));

        return parent::afterDelete();
    }
}
