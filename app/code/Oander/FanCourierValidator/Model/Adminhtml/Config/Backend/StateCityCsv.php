<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Model\Adminhtml\Config\Backend;

use Magento\Config\Model\Config\Backend\File;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManager;
use Oander\FanCourierValidator\Api\CityRepositoryInterface;
use Oander\FanCourierValidator\Api\Data\CityInterface;
use Oander\FanCourierValidator\Api\Data\StateCityInterface;
use Oander\FanCourierValidator\Api\Data\StateInterface;
use Oander\FanCourierValidator\Api\StateCityRepositoryInterface;
use Oander\FanCourierValidator\Api\StateRepositoryInterface;

/**
 * Class StateCityCsv
 * @package Oander\FanCourierValidator\Model\Adminhtml\Config\Backend
 */
class StateCityCsv extends File
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|CityRepositoryInterface
     */
    protected $cityRepository;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;
    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|StateRepositoryInterface
     */
    private $stateRepository;
    /**
     * @var array|StateCityRepositoryInterface
     */
    private $stateCityRepository;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var StoreManager
     */
    private $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var Data
     */
    private $directoryHelper;

    /**
     * @var \Magento\Directory\Model\Data\CountryInformationFactory
     */
    protected $countryInformationFactory;

    /**
     * @var \Magento\Directory\Model\Data\RegionInformationFactory
     */
    protected $regionInformationFactory;
    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        \Magento\Framework\Filesystem $filesystem,
        CityRepositoryInterface $cityRepository,
        StateRepositoryInterface $stateRepository,
        StateCityRepositoryInterface $stateCityRepository,
        StoreManager $storeManager,
        ScopeConfigInterface $scopeConfig,
        Data $directoryHelper,
        \Magento\Directory\Model\Data\CountryInformationFactory $countryInformationFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\Data\RegionInformationFactory $regionInformationFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\File\Csv $csv,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $uploaderFactory, $requestData, $filesystem, $resource, $resourceCollection, $data);
        $this->cityRepository = $cityRepository;
        $this->csv = $csv;
        $this->stateRepository = $stateRepository;
        $this->stateCityRepository = $stateCityRepository;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->directoryHelper = $directoryHelper;
        $this->countryInformationFactory = $countryInformationFactory;
        $this->regionInformationFactory = $regionInformationFactory;
        $this->countryFactory = $countryFactory;
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
        $file = $this->getFileData();
        if (!isset($file['tmp_name'])) {
            $value = $this->getValue();
            if (isset($value['delete']) && $value['delete'] == 1) {
                $this->cityRepository->truncate();
                $this->messageManager->addSuccessMessage(__('Cities removed successfully'));

                $this->stateRepository->truncate();
                $this->messageManager->addSuccessMessage(__('States removed successfully'));

                $this->stateCityRepository->truncate();
                $this->messageManager->addSuccessMessage(__('State-city pairs removed successfully'));
            }

            return parent::beforeSave();
        }

        $csvData = $this->csv->getData($file['tmp_name']);
        $stateIndex = 1;
        $cityIndex = 0;
        foreach ($csvData[0] as $key => $csvHeader) {
            switch ($csvHeader) {
                case 'Localitate':
                    $cityIndex = $key;
                    break;
                case 'Judet':
                    $stateIndex = $key;
                    break;
            }
        }
        unset($csvData[0]);

        $cities = [];
        $states = [];
        $stateCityPairs = [];
        $addedStates = [];
        foreach ($csvData as $entityId => $csvRow) {
            $cities[] = [
                CityInterface::ENTITY_ID => $entityId,
                CityInterface::CITY => $csvRow[$cityIndex]
            ];

            if (array_key_exists($csvRow[$stateIndex],$addedStates)) {
                $stateId = $addedStates[$csvRow[$stateIndex]];
            } else {
                $addedStates[$csvRow[$stateIndex]] = $entityId;
                $states[] = [
                    StateInterface::ENTITY_ID => $entityId,
                    StateInterface::STATE => $csvRow[$stateIndex]
                ];

                $stateId = $entityId;
            }

            $stateCityPairs[] = [
                StateCityInterface::ENTITY_ID => $entityId,
                StateCityInterface::CITY_ID => $entityId,
                StateCityInterface::STATE_ID => $stateId
            ];
        }
/*

        switch ($this->getScope()) {
            case 'website':
                $store = $this->storeManager->getWebsite($this->getScopeId())->getDefaultStore();
                break;
            case 'store':
                $store = $this->storeManager->getStore($this->getScopeId());
                break;
            default:
                $stores = $this->storeManager->getStores(false,true);
                $store = $stores['ro_ro'];
        }

        $storeLocale = $this->scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $store->getCode()
        );

        $defaultCountry = $this->scopeConfig->getValue(
            'general/country/default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $store->getCode()
        );

        $countriesCollection = $this->directoryHelper->getCountryCollection($store)->load();
        $country = $countriesCollection->getItemById($defaultCountry);

        $countryInfo = $this->countryInformationFactory->create();
        $countryInfo->setId($defaultCountry);
        $countryInfo->setTwoLetterAbbreviation($country->getData('iso2_code'));
        $countryInfo->setThreeLetterAbbreviation($country->getData('iso3_code'));
        $countryInfo->setFullNameLocale($country->getName($storeLocale));
        $countryInfo->setFullNameEnglish($country->getName('en_US'));

        $regionsInfo = [];
        foreach ($states as $id => $state) {
            $regionInfo = $this->regionInformationFactory->create();
            $regionInfo->setId($id);
            $regionInfo->setCode(strtoupper(substr($state, 0, 2)));
            $regionInfo->setName($state);
            $regionsInfo[] = $regionInfo;
        }
        $countryInfo->setAvailableRegions($regionsInfo);

        try {
            $countryModel = $this->countryFactory->create();
            $countryModel-
            $countryModel->getResource()->save($countryInfo);
            $countryInfo->getResource()->save($countryInfo);
        } catch (\Exception $exception) {
            $a = 22;
        }

        $b = 3;
*/
        $this->cityRepository->truncate();
        $insertedCities = $this->cityRepository->insertMultipleCities($cities);
        $this->messageManager->addSuccessMessage(__('%1 cities added successfully', $insertedCities));

        $this->stateRepository->truncate();
        $insertedStates = $this->stateRepository->insertMultipleStates($states);
        $this->messageManager->addSuccessMessage(__('%1 states added successfully', $insertedStates));

        $this->stateCityRepository->truncate();
        $insertedPairs = $this->stateCityRepository->insertMultipleStateCityPairs($stateCityPairs);
        $this->messageManager->addSuccessMessage(__('%1 state-city pair created successfully', $insertedPairs));

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
