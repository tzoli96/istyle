<?php
/**
 * Oander_DisabledProductPage
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\DisabledProductPage\Plugin\Amasty\Xnotif\Helper;

use Oander\DisabledProductPage\Helper\Product as ProductHelper;

/**
 * Class Data
 * @package Oander\DisabledProductPage\Plugin\Amasty\Xnotif\Helper
 */
class Data
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
     * @param \Amasty\Xnotif\Helper\Data $subject
     * @param $result
     * @return mixed|string
     */
    public function afterObserveStockAlertBlock(
        \Amasty\Xnotif\Helper\Data $subject,
        $result
    ) {
        if ($this->productHelper->isDisabledProductPage()) {
            $result = '';
        }

        return $result;
    }
}
