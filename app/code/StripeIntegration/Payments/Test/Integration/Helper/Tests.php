<?php

namespace StripeIntegration\Payments\Test\Integration\Helper;

class Tests
{
    protected $objectManager = null;
    protected $quoteRepository = null;
    protected $productRepository = null;

    public function __construct()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->quoteRepository = $this->objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->checkoutSession = $this->objectManager->get(\Magento\Checkout\Model\Session::class);
        $this->cartManagement = $this->objectManager->get(\Magento\Quote\Api\CartManagementInterface::class);
        $this->orderFactory = $this->objectManager->get(\Magento\Sales\Model\OrderFactory::class);
        $this->quoteManagement = $this->objectManager->get(\StripeIntegration\Payments\Test\Integration\Helper\QuoteManagement::class);
        $this->store = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore();
        $this->invoiceRepository = $this->objectManager->get(\Magento\Sales\Api\InvoiceRepositoryInterface::class);
        $this->creditmemoItemInterfaceFactory = $this->objectManager->get(\Magento\Sales\Api\Data\CreditmemoItemCreationInterfaceFactory::class);
        $this->refundOrder = $this->objectManager->get(\Magento\Sales\Api\RefundOrderInterface::class);
        $this->orderRepository = $this->objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $this->creditmemoFactory = $this->objectManager->get(\Magento\Sales\Model\Order\CreditmemoFactory::class);
        $this->creditmemoService = $this->objectManager->get(\Magento\Sales\Model\Service\CreditmemoService::class);
        $this->stripeConfig = $this->objectManager->get(\StripeIntegration\Payments\Model\Config::class);
        $this->eventFactory = $this->objectManager->get(\StripeIntegration\Payments\Test\Integration\Helper\EventFactory::class);
        $this->event = $this->eventFactory->create();
        $this->invoiceService = $this->objectManager->get(\Magento\Sales\Model\Service\InvoiceService::class);
    }

    public function refundOffline($invoice, $itemSkus)
    {
        $items = [];

        foreach ($invoice->getAllItems() as $invoiceItem)
        {
            if ($invoiceItem->getOrderItem()->getParentItem())
                continue;

            $sku = $invoiceItem->getSku();

            if(in_array($sku, $itemSkus))
            {
                $creditmemoItem = $this->creditmemoItemInterfaceFactory->create();
                $items[] = $creditmemoItem
                            ->setQty($invoiceItem->getQty())
                            ->setOrderItemId($invoiceItem->getOrderItemId());
            }
        }

        // Create the credit memo
        $this->refundOrder->execute($invoice->getOrderId(), $items, true, false);
    }

    public function refundOnline($invoice, $itemQtys, $shippingAmount = 0, $adjustmentPositive = 0, $adjustmentNegative = 0)
    {
        $qtys = [];

        foreach ($invoice->getAllItems() as $invoiceItem)
        {
            if ($invoiceItem->getOrderItem()->getParentItem())
                continue;

            $sku = $invoiceItem->getSku();

            if(isset($itemQtys[$sku]))
                $qtys[$invoiceItem->getOrderItem()->getId()] = $itemQtys[$sku];
        }

        $params = [
            "qtys" => $qtys,
            "shipping_amount" => $shippingAmount,
            "adjustment_positive" => $adjustmentPositive,
            "adjustment_negative" => $adjustmentNegative
        ];

        if (empty($invoice->getTransactionId()))
            throw new \Exception("Cannot refund online because the invoice has no transaction ID");

        $creditmemo = $this->creditmemoFactory->createByInvoice($invoice, $params);

        // Create the credit memo
        $this->creditmemoService->refund($creditmemo);
    }

    public function invoiceOnline($order, $itemQtys, $captureCase = \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE)
    {
        $orderItemIDs = [];
        $orderItemQtys = [];

        foreach ($order->getAllVisibleItems() as $orderItem)
        {
            $orderItemIDs[$orderItem->getSku()] = $orderItem->getId();
        }

        foreach ($itemQtys as $sku => $qty)
        {
            if (isset($orderItemIDs[$sku]))
            {
                $id = $orderItemIDs[$sku];
                $orderItemQtys[$id] = $qty;
            }
        }

        $invoice = $this->invoiceService->prepareInvoice($order, $orderItemQtys);
        $invoice->setRequestedCaptureCase($captureCase);
        $order->setIsInProcess(true);
        $invoice->register();
        $invoice->pay();
        $invoice->save();
        $order->save();
        return $invoice;
    }

    public function stripe()
    {
        return $this->stripeConfig->getStripeClient();
    }

    public function event()
    {
        return $this->event;
    }

    public function saveProduct($product)
    {
        return $this->productRepository->save($product);
    }

    public function getProduct($sku)
    {
        return $this->productRepository->get($sku);
    }

    public function getOrdersCount()
    {
        return $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();
    }

    public function getLastOrder()
    {
        return $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->setOrder('increment_id','DESC')->getFirstItem();
    }
}
