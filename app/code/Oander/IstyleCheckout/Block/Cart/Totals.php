<?php
namespace Oander\IstyleCheckout\Block\Cart;

use Magento\Checkout\Model\Cart;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Class Totals
 * @package Oander\IstyleCheckout\Block\Cart+
 */
class Totals extends Template
{
    protected $cart;
    protected $priceHelper;

    public function __construct(Context $context, Cart $cart, Data $priceHelper, array $data = []) {
        $this->cart = $cart;
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get price
     * @return Array
     */
    public function getPrice()
    {
        $subTotal = $this->cart->getQuote()->getBaseSubtotalWithDiscount();
        $grandTotal = $this->cart->getQuote()->getGrandTotal();

        return [
            "subTotal" => $this->getFormattedPrice($subTotal),
            "grandTotal" => $this->getFormattedPrice($grandTotal)
        ];
    }

    /**
     * Get formatted price
     * @param price Number
     * @return float
     */
    private function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * Has cart items
     * @return boolean
     */
    public function hasCartItems()
    {
        return ($this->cart->getQuote()->getGrandTotal() > 0) ? true : false;
    }
}
