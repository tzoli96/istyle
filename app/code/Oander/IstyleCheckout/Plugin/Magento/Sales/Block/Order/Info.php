<?php

namespace Oander\IstyleCheckout\Plugin\Magento\Sales\Block\Order;

use Magento\Sales\Model\Order\Address;
use Oander\PosLocations\Api\Data\ShopInterface;

class Info
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;
    /**
     * @var \Oander\IstyleCheckout\Helper\WarehousePos
     */
    private $warehousePosHelper;

    /**
     * Info constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Oander\IstyleCheckout\Helper\WarehousePos $warehousePosHelper
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Oander\IstyleCheckout\Helper\WarehousePos $warehousePosHelper
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->warehousePosHelper = $warehousePosHelper;
    }

    public function around__call(
        \Magento\Sales\Block\Order\Info $subject,
        \Closure $proceed,
        $method,
        $args
    ) {
        if($method=="isStorePickup")
        {
            $order = $this->_checkoutSession->getLastRealOrder();
            return $this->isStorePickup($order);
        }
        if($method=="getPosLocationData")
        {
            $order = $this->_checkoutSession->getLastRealOrder();
            return $this->getPosLocationData($order);
        }
        if($method=="getShippingTitle")
        {
            $order = $this->_checkoutSession->getLastRealOrder();
            return $this->getShippingTitle($order);
        }
        $result = $proceed($method, $args);
        return $result;
    }

    public function aroundGetShippingTitle(
        $subject,
        \Closure $proceed
    )
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        return $this->getShippingTitle($order);
    }

    /**
     * @return bool
     */
    public function aroundIsStorePickup(
        $subject,
        \Closure $proceed
    ) {
        $order = $this->_checkoutSession->getLastRealOrder();
        return $this->isStorePickup($order);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetPosLocationData(
        $subject,
        \Closure $proceed
    ) {
        $order = $this->_checkoutSession->getLastRealOrder();
        return $this->getPosLocationData($order);
    }

    public function aroundGetFormattedAddress(
        \Magento\Sales\Block\Order\Info $subject,
        \Closure $proceed,
        Address $address
    ) {
        if($address->getAddressType()==\Magento\Sales\Model\Order\Address::TYPE_SHIPPING)
        {
            if($this->isStorePickup($address->getOrder()))
            {
                $posData = $this->getPosLocationData($address->getOrder());
                if(is_array($posData) && count($posData)) {
                    $posData = $posData[0];
                    if (is_array($posData)) {
                        $output = "";
                        if(!empty($posData[ShopInterface::ADDRESS]))
                            $output .= '<div class="pos-address">' . $posData[ShopInterface::ADDRESS] . '</div>';
                        if(!empty($posData[ShopInterface::PARKING]))
                            $output .= '<div class="pos-info">' . $posData[ShopInterface::PARKING] . '</div>';
                        if(!empty($posData[ShopInterface::GOOGLE_TELEPHONE]))
                            $output .= '<div class="pos-phone">' . __('Phone') . ": " . $posData[ShopInterface::GOOGLE_TELEPHONE] . '</div>';
                        if (is_array($posData[ShopInterface::GOOGLE_OPENING])) {
                            $output .= '<div class="pos-opening-hours"><div class="opening-hours-title mb-1">Opening Hours</div>';
                            foreach ($posData[ShopInterface::GOOGLE_OPENING] as $openingItem) {
                                $openingItem = (array)$openingItem;
                                $output .= '<div class="opening-hours-item"><span>' . __($openingItem['row_name']) . '</span>';
                                if($openingItem['closes'] =="closed" || $openingItem['opening'] =="closed")
                                {
                                    $output .= '<div class="text-right"><span>' . __('Closed') . '</span></div>';
                                }
                                else
                                {
                                    $output .= '<div class="text-right"><span>' . date('H:i', strtotime($openingItem['opening'])) . '</span><span> - </span><span>' . date('H:i', strtotime($openingItem['closes'])) . '</span></div>';
                                }
                                $output .= '</div>';
                            }
                            $output .= '</div>';
                        }
                        return $output;
                    }
                }
            }
        }

        return $proceed($address);
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     * @return bool
     */
    private function isStorePickup($order)
    {
        if(strpos($order->getShippingMethod(), \Oander\WarehouseManager\Enum\CarrierMethod::PICKUP) !== false){
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
        if($this->isStorePickup($order))
        {
            $warehouseId = preg_replace('/[^0-9.]+/', '', $order->getShippingMethod());
            return $this->warehousePosHelper->getPosLocationInfo($warehouseId);
        }
        return false;
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     * @return \Magento\Framework\Phrase|null
     */
    private function getShippingTitle($order)
    {
        if($this->isStorePickup($order))
        {
            return __('Store Information');
        }
        return null;
    }

}