<?php
/**
 * Oander_DisabledProductPage
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\DisabledProductPage\Plugin\Magento\Magento\Framework;

use Oander\DisabledProductPage\Helper\Config as ConfigHelper;
use Oander\DisabledProductPage\Helper\Product as ProductHelper;

/**
 * Class Config
 * @package Oander\DisabledProductPage\Plugin\Magento\Magento\Framework
 */
class Config
{
    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var ConfigHelper
     */
    protected $config;

    /**
     * @param ProductHelper $productHelper
     * @param ConfigHelper $config
     */
    public function __construct(
        ProductHelper $productHelper,
        ConfigHelper $config
    ) {
        $this->productHelper = $productHelper;
        $this->config = $config;
    }

    /**
     * @param \Magento\Framework\View\Page\Config $subject
     * @param $result
     * @return mixed
     */
    public function afterGetRobots(
        \Magento\Framework\View\Page\Config $subject,
        $result
    ) {
        if ($this->productHelper->isDisabledProductPage()) {
            $result = $this->config->getIndexingRule();
        }

        return $result;
    }
}
