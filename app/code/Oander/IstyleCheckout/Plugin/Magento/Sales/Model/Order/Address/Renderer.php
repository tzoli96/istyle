<?php

namespace Oander\IstyleCheckout\Plugin\Magento\Sales\Model\Order\Address;

use Magento\Sales\Model\Order\Address;
use Oander\PosLocations\Api\Data\ShopInterface;

class Renderer
{
    /**
     * @var \Oander\IstyleCheckout\Helper\WarehousePos
     */
    private $warehousePosHelper;

    /**
     * Renderer constructor.
     * @param \Oander\IstyleCheckout\Helper\WarehousePos $warehousePosHelper
     */
    public function __construct(
        \Oander\IstyleCheckout\Helper\WarehousePos $warehousePosHelper
    ) {
        $this->warehousePosHelper = $warehousePosHelper;
    }

    public function aroundFormat(
        \Magento\Sales\Model\Order\Address\Renderer $subject,
        \Closure                        $proceed,
        Address $address,
        $type
    )
    {
        if($address->getAddressType()==\Magento\Sales\Model\Order\Address::TYPE_SHIPPING && $type == 'html') {
            if ($this->isStorePickup($address->getOrder())) {
                $posData = $this->getPosLocationData($address->getOrder());
                if (is_array($posData) && count($posData)) {
                    $posData = $posData[0];
                    if (is_array($posData)) {
                        $output = "";
                        if (!empty($posData[ShopInterface::ADDRESS]))
                            $output .= '<div class="pos-address">' . $posData[ShopInterface::ADDRESS] . '</div>';
                        if (!empty($posData[ShopInterface::PARKING]))
                            $output .= '<div class="pos-info">' . $posData[ShopInterface::PARKING] . '</div>';
                        if (!empty($posData[ShopInterface::GOOGLE_TELEPHONE]))
                            $output .= '<div class="pos-phone">' . __('Phone') . ": " . $posData[ShopInterface::GOOGLE_TELEPHONE] . '</div>';
                        return $output;
                    }
                }
            }
        }

        return $proceed($address,$type);
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     * @return bool
     */
    private function isStorePickup($order)
    {
        if (strpos($order->getShippingMethod(), \Oander\WarehouseManager\Enum\CarrierMethod::PICKUP) !== false) {
            return true;
        }

        return false;
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     * @return array|boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getPosLocationData($order)
    {
        if ($this->isStorePickup($order)) {
            $warehouseId = preg_replace('/[^0-9.]+/', '', $order->getShippingMethod());
            return $this->warehousePosHelper->getPosLocationInfo($warehouseId);
        }
        return false;
    }

}