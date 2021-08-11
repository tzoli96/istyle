<?php

namespace Oander\IstyleCheckout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Oander\PosLocations\Model\Shop;
use Oander\PosLocations\Model\Service\ShopRepository;
use Oander\WarehouseManager\Api\Data\WarehouseInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class WarehouseSaveBefore
 * @package Oander\IstyleCheckout\Observer
 */
class WarehouseSaveBefore implements ObserverInterface
{
    /**
     * @var ShopRepository
     */
    protected $posLocationShopRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * WarehouseSaveBefore constructor.
     * @param ShopRepository $posLocationShopRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ShopRepository $posLocationShopRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->posLocationShopRepository = $posLocationShopRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $warehouse = $observer->getObject();
        $posLocationId = $warehouse->getData(WarehouseInterface::POS_LOCATION_ID);
        $websiteIds = $warehouse->getData(WarehouseInterface::WEBSITE_ID);
        if ($posLocationId && $websiteIds) {
            $this->posLocationValidation($posLocationId, $websiteIds);
        }
    }

    /**
     * @param $posLocationId
     * @param $webSiteId
     * @return mixed
     * @throws \Exception
     */
    protected function posLocationValidation($posLocationId, $websiteIds)
    {
        /** @var Shop $shop */
        $shop = $this->posLocationShopRepository->getById($posLocationId);
        if ($shop->getId() === null) {
            throw new \Exception( __("Pos location with this id doesn't exist"));
        }

        foreach($websiteIds as $websiteId)
        {
            $websiteStores = $this->storeManager->getWebsite($websiteId)->getStoreIds();
            if(!in_array($shop->getStoreId(),$websiteStores))
            {
                throw new \Exception(__("Pos Location doesn't exist in the specific store(s)"));
            }
        }
    }

}
