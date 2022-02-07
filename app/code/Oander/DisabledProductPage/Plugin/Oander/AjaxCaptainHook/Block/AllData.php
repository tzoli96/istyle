<?php
/**
 * Oander_DisabledProductPage
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\DisabledProductPage\Plugin\Oander\AjaxCaptainHook\Block;

use Oander\DisabledProductPage\Helper\Product as ProductHelper;

/**
 * Class AllData
 * @package Oander\DisabledProductPage\Plugin\Oander\AjaxCaptainHook\Block
 */
class AllData
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
     * @param \Oander\AjaxCaptainHook\Block\AllData $subject
     * @param $result
     * @return mixed|string
     */
    public function afterIsEnabled(
        \Oander\AjaxCaptainHook\Block\AllData $subject,
        $result
    ) {
        if ($this->productHelper->isDisabledProductPage()) {
            $result = false;
        }

        return $result;
    }
}
