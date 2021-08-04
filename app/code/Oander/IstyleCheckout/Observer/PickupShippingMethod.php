<?php

namespace Oander\IstyleCheckout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Oander\IstyleCheckout\Helper\WarehousePos;

/**
 * Class PickupShippingMethod
 * @package Oander\IstyleCheckout\Observer
 */
class PickupShippingMethod implements ObserverInterface
{
    /**
     * @var WarehousePos
     */
    protected $warehousePosHelper;

    /**
     * PickupShippingMethod constructor.
     * @param WarehousePos $warehousePosHelper
     */
    public function __construct(
        WarehousePos $warehousePosHelper
    ) {
        $this->warehousePosHelper = $warehousePosHelper;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $methodChooser = $observer->getData('methodChooser');
        /** @var \Magento\Quote\Api\Data\ShippingMethodInterface $shippingMethod */
        $shippingMethod = $observer->getData('shippingMethod');

        if ($methodChooser && $shippingMethod) {
            $warehouseId = preg_replace('/[^0-9.]+/', '', $shippingMethod->getMethodCode());
            $methodChooser->setWarehouseManagerData([$this->warehousePosHelper->getPosLocationInfo($warehouseId)]);
            $observer->setData('methodChooser', $methodChooser);
        }
    }
}
