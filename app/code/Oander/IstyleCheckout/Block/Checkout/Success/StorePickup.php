<?php

namespace Oander\IstyleCheckout\Block\Checkout\Success;

use Magento\Framework\View\Element\Template;
use Oander\IstyleCheckout\Helper\WarehousePos;
use Oander\PosLocations\Api\ShopRepositoryInterface;
use Oander\PosLocations\Helper\Config;
use Oander\WarehouseManager\Api\WarehouseRepositoryInterface;

/**
 * Class StorePickup
 * @package Oander\IstyleCheckout\Block\OnePage\Success
 */
class StorePickup extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @var WarehousePos
     */
    protected $warehousePosHelper;

    /**
     * StorePickup constructor.
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param WarehouseRepositoryInterface $warehouseRepository
     * @param ShopRepositoryInterface $posLocationShopRepository
     * @param Config $posLocationHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config  $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        WarehousePos $warehousePosHelper,
        array $data = []
    ) {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
        $this->warehousePosHelper = $warehousePosHelper;
    }

    /**
     * @return bool
     */
    public function isStorePickup()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        if(strpos($order->getShippingMethod(), \Oander\WarehouseManager\Enum\CarrierMethod::PICKUP) !== false){
            return true;
        }

        return false;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPosLocationData()
    {
        $order = $this->_checkoutSession->getLastRealOrder();

        $warehouseId = preg_replace('/[^0-9.]+/', '', $order->getShippingMethod());
        return $this->warehousePosHelper->getPosLocationInfo($warehouseId);
    }
}