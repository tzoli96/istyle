<?php

namespace StripeIntegration\Payments\Model\Stripe;

class InvoiceItem extends StripeObject
{
    protected $objectSpace = 'invoiceItems';

    public function fromOrderItem($item, $order, $customerId)
    {
        $data = [
            'customer' => $customerId,
            'price_data' => [
                'currency' => $order->getOrderCurrencyCode(),
                'product' => $item->getProductId(),
                'unit_amount' => $this->helper->convertMagentoAmountToStripeAmount($item->getPrice(), $order->getOrderCurrencyCode(), $order)
            ],
            'currency' => $order->getOrderCurrencyCode(),
            'description' => $item->getName(),
            'quantity' => $item->getQtyOrdered()
        ];

        $this->createObject($data);

        if (!$this->object)
            throw new \Magento\Framework\Exception\LocalizedException(__("The invoice item for product \"%1\" could not be created in Stripe", $item->getName()));

        return $this;
    }

    public function fromTax($order, $customerId)
    {
        $currency = $order->getOrderCurrencyCode();
        $amount = $this->helper->convertMagentoAmountToStripeAmount($order->getTaxAmount(), $currency);
        if (!$amount || $amount <= 0)
            return $this;

        $data = [
            'customer' => $customerId,
            'amount' => $amount,
            'currency' => $currency,
            'description' => __("Tax")
        ];

        $this->createObject($data);

        if (!$this->object)
            throw new \Magento\Framework\Exception\LocalizedException(__("The tax for order #%1 could not be created in Stripe", $order->getIncrementId()));

        return $this;
    }

    public function fromShipping($order, $customerId)
    {
        $currency = $order->getOrderCurrencyCode();
        $amount = $this->helper->convertMagentoAmountToStripeAmount($order->getShippingAmount(), $currency);
        if (!$amount || $amount <= 0)
            return $this;

        $data = [
            'customer' => $customerId,
            'amount' => $amount,
            'currency' => $currency,
            'description' => __("Shipping")
        ];

        $this->createObject($data);

        if (!$this->object)
            throw new \Magento\Framework\Exception\LocalizedException(__("The shipping amount for order #%1 could not be created in Stripe", $order->getIncrementId()));

        return $this;
    }
}
