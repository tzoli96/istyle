<?php

namespace Oander\CofidisPayment\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Oander\CofidisPayment\Helper\Config;
use Oander\CofidisPayment\Controller\Product as ControllerProduct;

/**
 * Class Index
 * @package Oander\CofidisPayment\Block\Product
 */
class Index extends Template
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
     * @return array
     */
    public function getCalculatorData()
    {
        $postdata = array(
            'isEnabled' => $this->config->isEnabled(),
            'minimumTotal' => $this->config->getMinimumTotal(),
            'maximumTotal' => $this->config->getMaximumTotal(),
            'shopId'    => $this->config->getShopId(),
            'barem'     => $this->config->getConstructionGroup(),
            'downpmnt'  => $this->getRequest()->getParam("downpmnt", 0),
            'termsUrl'  => $this->config->getTermsUrl()
        );

        return $postdata;
    }

    public function getAjaxUrl()
    {
        return $this->getUrl("cofidis/product/index");
    }
}
