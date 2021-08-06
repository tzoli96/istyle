<?php

namespace Oander\IstyleCheckout\Block\Checkout;

/**
 * Class Success
 * @package Oander\IstyleCheckout\Block\Checkout
 */
class Success extends \Innobyte\CheckoutSuccess\Block\Onepage\Success
{
    /**
     * {@inheritdoc}
     */
    protected function prepareBlockData()
    {
        parent::prepareBlockData();

        $order = $this->_checkoutSession->getLastRealOrder();
        $this->addData([
            'first_name' => $order->getBillingAddress()->getFirstname()
        ]);
    }
}
