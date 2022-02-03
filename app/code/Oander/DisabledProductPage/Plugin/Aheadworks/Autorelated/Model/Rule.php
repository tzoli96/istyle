<?php
/**
 * Oander_DisabledProductPage
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\DisabledProductPage\Plugin\Aheadworks\Autorelated\Model;

use Oander\DisabledProductPage\Helper\Config;
use Oander\DisabledProductPage\Helper\Product as ProductHelper;

/**
 * Class Rule
 * @package Oander\DisabledProductPage\Plugin\Aheadworks\Autorelated\Model
 */
class Rule
{
    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param ProductHelper $productHelper
     * @param Config $config
     */
    public function __construct(
        ProductHelper $productHelper,
        Config $config
    ) {
        $this->productHelper = $productHelper;
        $this->config = $config;
    }

    /**
     * @param \Aheadworks\Autorelated\Model\Rule $subject
     * @param $result
     * @return mixed|string
     */
    public function afterGetTitle(
        \Aheadworks\Autorelated\Model\Rule $subject,
        $result
    ) {
        if ($this->productHelper->isDisabledProductPage()) {
            $result = $this->config->getSubstituteProductsTitle();
        }

        return $result;
    }
}
