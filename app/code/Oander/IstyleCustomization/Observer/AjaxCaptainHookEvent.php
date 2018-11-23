<?php
/**
 *   /$$$$$$   /$$$$$$  /$$   /$$ /$$$$$$$  /$$$$$$$$ /$$$$$$$
 *  /$$__  $$ /$$__  $$| $$$ | $$| $$__  $$| $$_____/| $$__  $$
 * | $$  \ $$| $$  \ $$| $$$$| $$| $$  \ $$| $$      | $$  \ $$
 * | $$  | $$| $$$$$$$$| $$ $$ $$| $$  | $$| $$$$$   | $$$$$$$/
 * | $$  | $$| $$__  $$| $$  $$$$| $$  | $$| $$__/   | $$__  $$
 * | $$  | $$| $$  | $$| $$\  $$$| $$  | $$| $$      | $$  \ $$
 * |  $$$$$$/| $$  | $$| $$ \  $$| $$$$$$$/| $$$$$$$$| $$  | $$
 *  \______/ |__/  |__/|__/  \__/|_______/ |________/|__/  |__/
 *
 * Oander_AjaxCaptianHook
 *
 * @author  Róbert Betlen <robert.betlen@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Json\DecoderInterface;

/**
 * Class AjaxCaptianHookEvent
 * @package Oander\IstyleCustomization\Observer
 */
class AjaxCaptainHookEvent implements ObserverInterface
{
    const INPUT_DISABLEPRICE = 'disableprice';
    const OUTPUT_NAME = 'price';

    const OUTPUT_NAME1 = 'price';
    const OUTPUT_NAME2 = 'oldprice';
    const OUTPUT_NAME3 = 'configprices';
    const OUTPUT_NAME4 = 'productviewconfig';

    /**
     * @var \Oander\ConfigurableProductAttribute\Magento\Swatches\Block\Product\Renderer\Configurable
     */
    private $configurable;
    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var \Magento\Catalog\Block\Product\View
     */
    private $productView;
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $catalogHelper;


    /**
     * AjaxCaptianHookEvent constructor.
     * @param \Oander\ConfigurableProductAttribute\Magento\Swatches\Block\Product\Renderer\Configurable $configurable
     * @param \Magento\Catalog\Block\Product\View $productView
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param DecoderInterface $jsonDecoder
     */

    public function __construct(
        \Oander\ConfigurableProductAttribute\Magento\Swatches\Block\Product\Renderer\Configurable $configurable,
        \Magento\Catalog\Block\Product\View $productView,
        \Magento\Catalog\Helper\Data $catalogHelper,
        DecoderInterface $jsonDecoder
    )
    {
        $this->configurable = $configurable;
        $this->jsonDecoder = $jsonDecoder;
        $this->productView = $productView;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $input = $observer->getData('input');
        $output = $observer->getData('output');
        /** @var \Magento\Catalog\Model\Product $product */
        $params = $input->getData('params');
        $disableprice = isset($params['data'])?(is_array($params['data'])?in_array(self::INPUT_DISABLEPRICE, $params['data']):false):false;
        if(!$disableprice) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $input->getData('product');
            /*$finalprice = (int)round($this->catalogHelper->getTaxPrice($product, $product->getFinalPrice(), true);
            $price = (int)$this->catalogHelper->getTaxPrice($product, $product->getPrice(), true);*/
            $price = $product->getPriceInfo()->getPrice('regular_price');
            $finalprice = $product->getPriceInfo()->getPrice('final_price');
            $price = $this->_registerJsPrice($price->getAmount()->getValue());
            $finalprice = $this->_registerJsPrice($finalprice->getAmount()->getValue());
            $productViewConfig = $this->jsonDecoder->decode($this->productView->getJsonConfig());

            /** @var \Magento\Catalog\Model\Product|null $realproduct */
            $realproduct = $input->getData('realproduct');
            if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
                if ($realproduct) {
                    if ($realproduct->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                        $this->configurable->setData('product', $realproduct);
                        $jsonData = $this->jsonDecoder->decode($this->configurable->getJsonConfig());
                        $output->setData(
                            self::OUTPUT_NAME,
                            array(
                                self::OUTPUT_NAME1 => $finalprice,
                                self::OUTPUT_NAME2 => $price,
                                self::OUTPUT_NAME3 => $jsonData,
                                self::OUTPUT_NAME4 => $productViewConfig,
                            )
                        );
                    }
                } else {
                    $output->setData(
                        self::OUTPUT_NAME,
                        array(
                            self::OUTPUT_NAME1 => $finalprice,
                            self::OUTPUT_NAME2 => $price,
                            self::OUTPUT_NAME4 => $productViewConfig,
                        )
                    );
                }
            } else {
                if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    $this->configurable->setData('product', $product);
                    $jsonData = $this->jsonDecoder->decode($this->configurable->getJsonConfig());
                    $output->setData(
                        self::OUTPUT_NAME,
                        array(
                            self::OUTPUT_NAME1 => $finalprice,
                            self::OUTPUT_NAME2 => $price,
                            self::OUTPUT_NAME3 => $jsonData,
                            self::OUTPUT_NAME4 => $productViewConfig,
                        )
                    );
                } else {
                    $output->setData(
                        self::OUTPUT_NAME,
                        array(
                            self::OUTPUT_NAME1 => $finalprice,
                            self::OUTPUT_NAME2 => $price,
                            self::OUTPUT_NAME4 => $productViewConfig,
                        )
                    );
                }
            }
        }
    }

    /**
     * Replace ',' on '.' for js
     *
     * @param float $price
     * @return string
     */
    protected function _registerJsPrice($price)
    {
        return str_replace(',', '.', $price);
    }
}
