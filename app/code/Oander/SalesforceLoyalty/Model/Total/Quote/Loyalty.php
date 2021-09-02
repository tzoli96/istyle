<?php
namespace Oander\SalesforceLoyalty\Model\Total\Quote;
use Oander\SalesforceLoyalty\Helper\Config;

/**
 * Class Custom
 * @package Mageplaza\HelloWorld\Model\Total\Quote
 */
class Loyalty extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @var \Oander\SalesforceLoyalty\Helper\Data
     */
    private $loyaltyHelper;
    /**
     * @var Config
     */
    private $configHelper;

    /**
     * Custom constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param Config $configHelper
     * @param \Oander\SalesforceLoyalty\Helper\Data $loyaltyHelper
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Oander\SalesforceLoyalty\Helper\Config $configHelper,
        \Oander\SalesforceLoyalty\Helper\Data $loyaltyHelper
    ){
        $this->_priceCurrency = $priceCurrency;
        $this->loyaltyHelper = $loyaltyHelper;
        $this->configHelper = $configHelper;
    }
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this|bool
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);

        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        //Check maximum redeemable points, and remove if can not be used
        if($this->configHelper->isSpendingEnabled() && ($this->loyaltyHelper->getMaxRedeemablePoints($quote->getGrandTotal()) >= $quote->getData(\Oander\SalesforceLoyalty\Enum\Attribute::LOYALTY_DISCOUNT))) {
            $baseDiscount = $quote->getData(\Oander\SalesforceLoyalty\Enum\Attribute::LOYALTY_DISCOUNT);
            $discount = $this->_priceCurrency->convert($baseDiscount);
        }
        else
        {
            $quote->setData(\Oander\SalesforceLoyalty\Enum\Attribute::LOYALTY_DISCOUNT, 0);
            $baseDiscount = 0;
            $discount = 0;
        }
        $total->setTotalAmount('loyalty_discount', -$discount);
        $total->setBaseTotalAmount('loyalty_discount', -$baseDiscount);
        $total->setLoyaltyDiscount($baseDiscount);
        return $this;
    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $amount = $total->getLoyaltyDiscount();

        if ($amount)
        {
            $result = [
                'code' => $this->getCode(),
                'title' => __('Loyalty Discount'),
                'value' => $amount
            ];
        }
        return $result;
    }

    /**
     * @param $quote \Magento\Quote\Model\Quote
     */
    private function needRemoveLoyaltyPoints($quote)
    {

    }

    public function getLabel()
    {
        return __('Loyalty Discount');
    }
}