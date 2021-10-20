<?php
namespace Oander\SalesforceLoyalty\Model\Total\Quote;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Oander\SalesforceLoyalty\Enum\Attribute;
use Oander\SalesforceLoyalty\Helper\Config;
use Oander\SalesforceLoyalty\Helper\Data;
use Magento\Framework\Registry;

/**
 * @package Oander\SalesforceLoyalty\Model\Total\Quote
 */
class Loyalty extends AbstractTotal
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @var Data
     */
    private $loyaltyHelper;
    /**
     * @var Config
     */
    private $configHelper;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param Config $configHelper
     * @param Data $loyaltyHelper
     * @param Registry $registry
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        Config $configHelper,
        Data $loyaltyHelper,
        Registry $registry
    ){
        $this->_priceCurrency = $priceCurrency;
        $this->loyaltyHelper = $loyaltyHelper;
        $this->configHelper = $configHelper;
        $this->registry = $registry;
    }
    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this|bool
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    )
    {
        /**
         * todo: - quote attributum itt tároljuk az a pontot amit beír fe-n át kell számítani devizára.
         *
         */
        parent::collect($quote, $shippingAssignment, $total);
        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $this->_getAddressItems($address);

        if (!count($items) || !$this->configHelper->isSpendingEnabled() || !$this->configHelper->getLoyaltyServiceEnabled() &&
            !$this->loyaltyHelper->getMaxRedeemablePoints($quote) >= $quote->getData(Attribute::LOYALTY_POINT)) {
            return $this;
        }
        $baseDiscount = $quote->getData(Attribute::LOYALTY_DISCOUNT);
        $discount = $this->_priceCurrency->convert($baseDiscount);
        $total->setTotalAmount('loyalty_discount', -$discount);
        $total->setBaseTotalAmount('loyalty_discount', -$baseDiscount);
        $total->setLoyaltyDiscount($baseDiscount);
        return $this;
    }

    public function fetch(Quote $quote, Total $total)
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

    public function getLabel()
    {
        return __('Loyalty Discount');
    }
}