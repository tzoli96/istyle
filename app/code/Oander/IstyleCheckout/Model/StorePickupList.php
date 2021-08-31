<?php
namespace Oander\IstyleCheckout\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;

class StorePickupList implements ConfigProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;
    /**
     * @var \Oander\WarehouseManager\Api\WarehouseRepositoryInterface
     */
    private $warehouseRepository;
    /**
     * @var \Oander\PosLocations\Model\ResourceModel\Shop\Collection
     */
    private $shopCollection;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * WebsiteIdConfigProvider constructor.
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Oander\WarehouseManager\Api\WarehouseRepositoryInterface $warehouseRepository
     * @param \Oander\PosLocations\Model\ResourceModel\Shop\Collection $shopCollection
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Oander\WarehouseManager\Api\WarehouseRepositoryInterface $warehouseRepository,
        \Oander\PosLocations\Model\ResourceModel\Shop\Collection $shopCollection
    ){
        $this->storeManager = $storeManager;
        $this->warehouseRepository = $warehouseRepository;
        $this->shopCollection = $shopCollection;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $config['istyle_checkout']['stores'] = $this->getStoreDetails($this->storeManager->getStore()->getWebsiteId());

        return $config;
    }

    /**
     * @param $websiteId int
     */
    public function getStoreDetails($websiteId)
    {
        $shopIds = [];
        $store = $this->scopeConfig->getValue('general/store_information', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $warehouses = $this->warehouseRepository->getAllByWebsiteId($websiteId);
        foreach ($warehouses as $warehouse)
        {
            $shopIds[] = $warehouse->getData(\Oander\WarehouseManager\Api\Data\WarehouseInterface::POS_LOCATION_ID);
        }
        $this->shopCollection->addFieldToFilter(\Oander\PosLocations\Api\Data\ShopInterface::SHOP_ID, array('in' => $shopIds));
        $shopItems = $this->shopCollection->getItems();
        $result = [];
        /** @var  $warehouse \Oander\WarehouseManager\Api\Data\WarehouseInterface */
        foreach ($warehouses as $warehouse)
        {
            if(isset($shopItems[$warehouse->getData(\Oander\WarehouseManager\Api\Data\WarehouseInterface::POS_LOCATION_ID)]))
            {
                /** @var \Oander\PosLocations\Api\Data\ShopInterface $shop */
                $shop = $shopItems[$warehouse->getData(\Oander\WarehouseManager\Api\Data\WarehouseInterface::POS_LOCATION_ID)];
                if(
                    !empty($shop->getGoogleCity()) &&
                    !empty($shop->getFistname()) &&
                    !empty($shop->getLastname()) &&
                    !empty($shop->getGooglePostalCode()) &&
                    !empty($shop->getGoogleAddress()) &&
                    !empty($shop->getGoogleTelephone())
                ) {
                    $result[$warehouse->getId()]['city'] = $shop->getGoogleCity();
                    $result[$warehouse->getId()]['countryId'] = $this->scopeConfig->getValue(
                        'general/country/default',
                        \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES
                    );
                    $result[$warehouse->getId()]['firstname'] = $shop->getFistname();
                    $result[$warehouse->getId()]['lastname'] = $shop->getLastname();
                    $result[$warehouse->getId()]['postcode'] = $shop->getGooglePostalCode();
                    $result[$warehouse->getId()]['regionId'] = 0;
                    $result[$warehouse->getId()]['saveInAddressBook'] = 0;
                    $result[$warehouse->getId()]['street'] = [$shop->getGoogleAddress()];
                    $result[$warehouse->getId()]['telephone'] = $shop->getGoogleTelephone();
                }
            }
            if(!isset($result[$warehouse->getId()]))
            {
                $result[$warehouse->getId()]['city'] = $store['city'];
                $result[$warehouse->getId()]['countryId'] = $store['country_id'];
                $result[$warehouse->getId()]['firstname'] = $store['name'];
                $result[$warehouse->getId()]['lastname'] = $store['name'];
                $result[$warehouse->getId()]['postcode'] = $store['postcode'];
                $result[$warehouse->getId()]['regionId'] = empty($store['region_id'])?0:$store['region_id'];
                $result[$warehouse->getId()]['saveInAddressBook'] = 0;
                $result[$warehouse->getId()]['street'] = [$store['street_line1']];
                $result[$warehouse->getId()]['telephone'] = $store['phone'];
            }
        }
        return $result;
    }
} 
