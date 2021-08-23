<?php

namespace Oander\IstyleCheckout\Block\Checkout\Success;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Oander\IstyleCheckout\Helper\WarehousePos;
use Oander\PosLocations\Api\ShopRepositoryInterface;
use Oander\PosLocations\Helper\Config;
use Oander\WarehouseManager\Api\WarehouseRepositoryInterface;

/**
 * Class StorePickup
 * @package Oander\IstyleCheckout\Block\OnePage\Success
 */
class StorePickupInfo extends \Magento\Sales\Block\Order\Info
{
    /**
     * @var WarehousePos
     */
    protected $warehousePosHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * StorePickupInfo constructor.
     * @param TemplateContext $context
     * @param Registry $registry
     * @param PaymentHelper $paymentHelper
     * @param AddressRenderer $addressRenderer
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param WarehousePos $warehousePosHelper
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        PaymentHelper $paymentHelper,
        AddressRenderer $addressRenderer,
        \Magento\Checkout\Model\Session $checkoutSession,
        WarehousePos $warehousePosHelper,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $paymentHelper, $addressRenderer, $data);
        $this->warehousePosHelper = $warehousePosHelper;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $infoBlock = $this->paymentHelper->getInfoBlock($this->getOrder()->getPayment(), $this->getLayout());
        $this->setChild('payment_info', $infoBlock);
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
