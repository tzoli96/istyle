<?php

namespace Oander\OneyThreeByFourExtender\Observer;

use Magento\Framework\App\Area;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\BlockFactory;
use Oander\OneyThreeByFourExtender\Helper\Data;

class AjaxCaptainHookEvent implements ObserverInterface
{
    const OUTPUT_NAME = 'oney';

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param BlockFactory $blockFactory
     * @param Data $helper
     */
    public function __construct(
        BlockFactory $blockFactory,
        Data $helper
    ) {
        $this->blockFactory = $blockFactory;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $input = $observer->getData('input');
        $output = $observer->getData('output');

        try {
            if($input->getData('product')) {
                $product = $input->getData('product');
                $product->unsetData('_cache_instance_options_collection');
                $price = $this->helper->getProductFinalPrice($product, $input->getData('params'));

                $block = $this->blockFactory
                    ->createBlock(\Oander\OneyThreeByFourExtender\Block\Catalog\Product::class)
                    ->setData(
                        [
                            'area' => Area::AREA_FRONTEND,
                            'productFinalPrice' => $price
                        ]
                    );
                $block->setTemplate('Oander_OneyThreeByFourExtender::catalog/product.phtml');

                if ($block->toHtml() != "") {
                    $output->setData(self::OUTPUT_NAME, $block->toHtml());
                }
            }
        } catch (\Exception $exception) {

        }
    }
}