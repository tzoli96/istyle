<?php
/**
 * Oander_DisabledProductPage
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\DisabledProductPage\Block\Product;

use Magento\Framework\View\Element\Template;
use Oander\DisabledProductPage\Helper\Config;

/**
 * Class Alert
 * @package Oander\DisabledProductPage\Block\Product
 */
class Alert extends Template
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Config $config,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getOutOfStockText()
    {
        return $this->config->getOutOfStockText();
    }
}
