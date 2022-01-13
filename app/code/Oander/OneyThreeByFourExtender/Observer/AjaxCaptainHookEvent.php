<?php

namespace Oander\OneyThreeByFourExtender\Observer;

use Magento\Framework\App\Area;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\BlockFactory;
use Oander\OneyThreeByFourExtender\Helper\Data;

class AjaxCaptainHookEvent implements ObserverInterface
{
    const OUTPUT_NAME_PRODUCT = 'oney_product';
    const OUTPUT_NAME_SIMULATION = 'oney_simulation';

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

                $productBlock = $this->blockFactory
                    ->createBlock(\Oander\OneyThreeByFourExtender\Block\Catalog\Product::class)
                    ->setData(
                        [
                            'area' => Area::AREA_FRONTEND,
                            'productFinalPrice' => $price
                        ]
                    );
                $productBlock->setTemplate('Oander_OneyThreeByFourExtender::catalog/product.phtml');

                $simulationBlock = $this->blockFactory
                    ->createBlock(\Oander\OneyThreeByFourExtender\Block\Catalog\Simulation::class)
                    ->setData(
                        [
                            'area' => Area::AREA_FRONTEND,
                            'productFinalPrice' => $price
                        ]
                    );
                $simulationBlock->setTemplate('Oander_OneyThreeByFourExtender::catalog/simulation.phtml');

                if ($productBlock->toHtml() != "") {
                    $output->setData(self::OUTPUT_NAME_PRODUCT, $productBlock->toHtml());
                } else {
                    $output->setData(self::OUTPUT_NAME_PRODUCT, '<div class="oney-widget"></div>');
                }

                if ($simulationBlock->toHtml() != "") {
                    $output->setData(self::OUTPUT_NAME_SIMULATION, $simulationBlock->toHtml());
                } else {
                    $output->setData(self::OUTPUT_NAME_SIMULATION, '<div class="oney-popup"></div>');
                }
            }
        } catch (\Exception $exception) {

        }
    }
}