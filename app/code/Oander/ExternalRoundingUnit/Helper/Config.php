<?php

namespace Oander\ExternalRoundingUnit\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Oander\ExternalRoundingUnit\Enum\Config as ConfigEnum;
use Oander\CurrencyManager\Model\PriceFactory;
use Magento\Store\Model\StoreManagerInterface;

class Config extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $general;

    /**
     * @var PriceFactory
     */
    private $price;

    /**
     * @param Context $context
     * @param PriceFactory $priceFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context               $context,
        PriceFactory          $priceFactory,
        StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
        parent::__construct($context);
        $this->price = $priceFactory->create();
        $this->general = (array)$this->scopeConfig->getValue(
            ConfigEnum::GENERAL_PATH,
            ScopeInterface::SCOPE_STORE
        );
        $this->price->setCurrency($this->storeManager->getStore()->getCurrentCurrency()->getCode());
    }

    /**
     * @return bool
     */
    public function IsEnabled(): bool
    {
        return (bool)$value = $this->general[ConfigEnum::GENERAL_ENABLED] ?? false;
    }

    /**
     * @return string
     */
    public function getRoundingRule(): string
    {
        return (string)$value = $this->general[ConfigEnum::ROUNDING_RULE] ?? '';
    }

    /**
     * @param $total
     * @return float
     */
    public function getRounding($total)
    {
        switch ($this->general[ConfigEnum::ROUNDING_RULE]) {
            case 1:
                $totals = round($total * 2, -1) * 0.5;
                break;
            case 2:
                $totals = round($total * 2, 0) * 0.5;
                break;
            case 3:
                $totals = $total;
                break;
            case 4:
                $totals = round($total);
                break;
            case 5:
                $totals = round($total, 1);
                break;
            case 6:
                $totals = round($total, 2);
                break;
            default:
                $totals = round($total);
        }
        return $totals;
    }

    public function getFormatNumber($total)
    {
        $total = number_format(
            (float)$total,
            $this->price->getPrecision(),
            $this->price->getDecimalSymbol(),
            $this->price->getGroupSymbol()
        );
        return $total;
    }

    /**
     * @param string $path
     * @param string $storeCode
     *
     * @return mixed
     */
    public function getValue(string $path, string $storeCode = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
}
