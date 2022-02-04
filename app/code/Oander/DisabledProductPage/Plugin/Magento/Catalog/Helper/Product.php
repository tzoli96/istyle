<?php
/**
 * Oander_DisabledProductPage
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\DisabledProductPage\Plugin\Magento\Catalog\Helper;

use Oander\DisabledProductPage\Helper\Product as ProductHelper;

/**
 * Class Product
 * @package Oander\DisabledProductPage\Plugin\Magento\Catalog\Helper
 */
class Product
{
    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @param ProductHelper $productHelper
     */
    public function __construct(
        ProductHelper $productHelper
    ) {
        $this->productHelper = $productHelper;
    }

    /**
     * @param \Magento\Catalog\Helper\Product $subject
     * @param $result
     * @return bool|mixed
     */
    public function afterCanShow(
        \Magento\Catalog\Helper\Product $subject,
        $result
    ) {
        if ($this->productHelper->isDisabledProductPage()) {
            $result = true;
        }

        return $result;
    }
}
