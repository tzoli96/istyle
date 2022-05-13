<?php

namespace Oander\ExternalRoundingUnit\Model\Total\Quote;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Oander\ExternalRoundingUnit\Helper\Config;
use Magento\Quote\Api\Data\TotalsInterface;

class ExternalRounding extends AbstractTotal
{
    const SALES_CODE = "external_rounding";

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @var TotalsInterface
     */
    protected $quoteTotal;

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
     * @param Registry $registry
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        Config                 $configHelper,
        Registry               $registry,
        TotalsInterface        $quoteTotal
    )
    {
        $this->_priceCurrency = $priceCurrency;
        $this->configHelper = $configHelper;
        $this->registry = $registry;
        $this->quoteTotal = $quoteTotal;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this|bool
     */
    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);
        if (!$this->configHelper->IsEnabled()) {
            return $this;
        }
        return $this;
    }

    public function fetch(Quote $quote, Total $total)
    {
        $result = null;
        $amount = $total->getData(self::SALES_CODE);

        if ($amount) {
            $result = [
                'code' => $this->getCode(),
                'title' => __('External Rounding'),
                'value' => $amount
            ];
        }
        return $result;
    }

    public function getLabel()
    {
        return __('External Rounding');
    }
}