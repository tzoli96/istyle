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
 * Oander_IstyleCustomization
 *
 * @author  Pinczés János <janos.pinczes@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\IstyleCustomization\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Json\DecoderInterface;
use Oander\AjaxCaptainHook\Controller\AjaxHook\ProductPageHook;
use Oander\ConfigurableProductAttribute\Magento\Swatches\Block\Product\Renderer\Configurable;

/**
 * Class AjaxCaptainHookEventDefaultSelection
 * @package Oander\IstyleCustomization\Observer
 */
class AjaxCaptainHookEventProductDefault implements ObserverInterface
{
    const OUTPUT_NAME = 'w_product_default';

    const OUTPUT_NAME1 = 'ajax';
    const OUTPUT_NAME2 = 'id';

    /**
     * @var Configurable
     */
    private $configurable;
    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    private $sortAttributeTemp = null;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var Manager
     */
    private $eventManager;


    /**
     * AjaxCaptianHookEvent constructor.
     * @param Configurable $configurable
     * @param ProductRepositoryInterface $productRepository
     * @param Manager $eventManager
     * @param DecoderInterface $jsonDecoder
     */

    public function __construct(
        Configurable $configurable,
        ProductRepositoryInterface $productRepository,
        Manager $eventManager,
        DecoderInterface $jsonDecoder
    )
    {
        $this->configurable = $configurable;
        $this->jsonDecoder = $jsonDecoder;
        $this->productRepository = $productRepository;
        $this->eventManager = $eventManager;
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
        $firstload = isset($params['data'])?(is_array($params['data'])?in_array(\Oander\AjaxCaptainHook\Observer\AjaxCaptainHookJsEventFirstLoad::INPUT_FIRSTLOAD, $params['data']):false):false;
        if($firstload) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $input->getData('product');
            if ($product->getTypeId() != 'simple' && $product->getTypeId() != 'virtual') {
                if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {

                    $this->configurable->setData('product', $product);
                    $jsonData = $this->jsonDecoder->decode($this->configurable->getJsonConfig());

                    $sortableArray = [];
                    foreach($jsonData['index'] as $key => $value)
                    {
                        $sortableArray[$key]['id'] = $key;
                        $sortableArray[$key]['price'] = $jsonData['optionPrices'][$key]['finalPrice']['amount'];
                        foreach($jsonData['attributes'] as $attributeid => $attributedata)
                        {
                            foreach($attributedata['options'] as $optionindex => $optiondata)
                            {
                                if($value[$attributeid] == $optiondata['id'])
                                {
                                    $sortableArray[$key][$attributeid] = $optionindex;
                                    break;
                                }
                            }
                        }
                    }
                    $this->sortPriceAttributeArray(array_keys($jsonData['attributes']), $sortableArray);
                    if(count($sortableArray)>0) {
                        $inputdefaultproduct = new \Magento\Framework\DataObject();
                        $outputdefaultproduct = new \Magento\Framework\DataObject();
                        $defaultProduct = $this->productRepository->getById($sortableArray[0]['id']);
                        $inputdefaultproduct->setData(ProductPageHook::EVENT_INPUT_PARAMS, ['data' => [\Oander\AjaxCaptainHook\Observer\AjaxCaptainHookJsEventFirstLoad::INPUT_FIRSTLOAD]]);
                        $inputdefaultproduct->setData(ProductPageHook::EVENT_INPUT_REALPRODUCT, null);
                        $inputdefaultproduct->setData(ProductPageHook::EVENT_INPUT_PRODUCT, $defaultProduct);
                        $this->eventManager->dispatch(ProductPageHook::AJAX_C_H_EVENT, [ProductPageHook::EVENT_INPUT => $inputdefaultproduct, ProductPageHook::EVENT_OUTPUT => $outputdefaultproduct]);
                        $output->setData(
                            self::OUTPUT_NAME,
                            array(
                                self::OUTPUT_NAME1 => $outputdefaultproduct->getData(),
                                self::OUTPUT_NAME2 => $defaultProduct->getId()
                            )
                        );
                    }
                }
            }
        }
    }

    private function sortPriceAttributeArray($attributes, &$array)
    {
        for($i = (count($attributes) -1); $i >= 0; $i--)
        {
            $this->sortAttributeTemp = $attributes[$i];
            usort($array, function (array $a, array $b) { return $a[$this->sortAttributeTemp] - $b[$this->sortAttributeTemp]; });
        }
        usort($array, function (array $a, array $b) { return $a["price"] - $b["price"]; });
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
