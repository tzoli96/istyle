<?php
/**
 * Oander_DisabledProductPage
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\DisabledProductPage\Plugin\Magento\Catalog\Block\Product\View;

use Oander\DisabledProductPage\Helper\Product as ProductHelper;

/**
 * Class Product
 * @package Oander\DisabledProductPage\Plugin\Magento\Catalog\Helper
 */
class Gallery
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
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param $result
     * @return bool|mixed
     */
    public function afterGetGalleryImagesJson(
        \Magento\Catalog\Block\Product\View\Gallery $subject,
        $result
    ) {
        if ($this->productHelper->isDisabledProductPage()) {
            $result = json_decode($result);
            $result['disabled_product'] = true;
            $result =  json_encode($result);
        }

        return $result;
    }
}
