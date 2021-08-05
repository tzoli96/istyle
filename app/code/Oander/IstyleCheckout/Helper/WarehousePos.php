<?php

namespace Oander\IstyleCheckout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Oander\PosLocations\Api\Data\ShopInterface;
use Oander\PosLocations\Api\ShopRepositoryInterface;
use Oander\PosLocations\Enum\Config as ConfigEnum;
use Oander\PosLocations\Helper\Config;
use Oander\PosLocations\Model\Shop;
use Oander\WarehouseManager\Api\WarehouseRepositoryInterface;
use Oander\WarehouseManager\Model\Warehouse;

/**
 * Class WarehousePos
 * @package Oander\IstyleCheckout\Helper
 */
class WarehousePos extends AbstractHelper
{
    /**
     * @var WarehouseRepositoryInterface
     */
    protected $warehouseRepository;

    /**
     * @var ShopRepositoryInterface
     */
    protected $posLocationShopRepository;

    /**
     * @var Config
     */
    protected $posLocationHelper;


    /**
     * WarehousePos constructor.
     * @param WarehouseRepositoryInterface $warehouseRepository
     * @param ShopRepositoryInterface $posLocationShopRepository
     * @param Config $posLocationHelper
     * @param Context $context
     */
    public function __construct(
        WarehouseRepositoryInterface $warehouseRepository,
        ShopRepositoryInterface $posLocationShopRepository,
        Config $posLocationHelper,
        Context $context
    ) {
        parent::__construct($context);
        $this->warehouseRepository = $warehouseRepository;
        $this->posLocationShopRepository = $posLocationShopRepository;
        $this->posLocationHelper = $posLocationHelper;
    }

    /**
     * @param $warehouseId
     * @return false|mixed
     */
    public function getPosLocationFromWarehouseId($warehouseId)
    {
        /** @var Warehouse $warehouse */
        $warehouse = $this->warehouseRepository->getById($warehouseId);
        $posLocationId = $warehouse->getPosLocation();
        if ($posLocationId == 0) {
            return false;
        }
        try {
            /** @var Shop $shop */
            return $this->posLocationShopRepository->getById($posLocationId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @param $warehouseId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getPosLocationInfo($warehouseId)
    {
        $posLocationInfo = false;
        $posLocation = $this->getPosLocationFromWarehouseId($warehouseId);
        if ($posLocation) {
            $posLocationInfo = [
                [
                    ShopInterface::ADDRESS => $posLocation->getAddress(),
                    ShopInterface::GEO_ADDRESS => $posLocation->getGeoAddress(),
                    ShopInterface::PARKING => $posLocation->getParking(),
                    ShopInterface::GEO_CODES => $posLocation->getGeoCodes(),
                    ShopInterface::GOOGLE_OPENING => $posLocation->getGoogleOpening(),
                    ShopInterface::GOOGLE_TELEPHONE => $posLocation->getGoogleTelephone(),
                    ConfigEnum::GENERAL_SETTINGS_PIN_IMAGE => $this->posLocationHelper->getPinImage(),
                    ConfigEnum::GENERAL_SETTINGS_PIN_WIDTH => $this->posLocationHelper->getPinWidth(),
                    ConfigEnum::GENERAL_SETTINGS_MAP_ZOOM => $this->posLocationHelper->getMapZoom()
                ]
            ];
        }

        return $posLocationInfo;
    }
}