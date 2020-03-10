<?php

namespace Oander\OtpCalculator\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Oander\OtpCalculator\Enum\Iframe;
use Oander\OtpCalculator\Helper\Config;

/**
 * Class Calculator
 * @package Oander\OtpCalculator\Block\Product
 */
class Calculator extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getProductPrice()
    {
        /** @var Product $product */
        $product = $this->registry->registry('current_product');

        return round($product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue());
    }

    /**
     * @return bool
     */
    public function isShow()
    {
        $productPrice = $this->getProductPrice();
        if ($this->config->isProductEnabled()
            && $this->config->getMinPrice() <= $productPrice
            && $this->config->getMaxPrice() >= $productPrice
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getIframeUrl()
    {
        return sprintf(Iframe::URL_WITH_PARAMS,$this->getProductPrice(),$this->config->getConstructionGroup(),$this->config->getRetailerId(),$this->config->getTerm());
    }
}
