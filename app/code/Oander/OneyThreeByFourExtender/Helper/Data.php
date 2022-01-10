<?php

namespace Oander\OneyThreeByFourExtender\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Item\Processor
     */
    protected $itemProcessor;

    public function __construct(
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Quote\Model\Quote\Item\Processor $itemProcessor,
        Context $context
    ) {
        parent::__construct($context);
        $this->objectFactory = $objectFactory;
        $this->itemProcessor = $itemProcessor;
    }

    /**
     * @param $product
     * @param $params
     * @return mixed
     */
    public function getProductFinalPrice($product, $params = [])
    {
        $finalPrice = $product->getFinalPrice();

        if ($product->getTypeId() == 'bundle') {
            if (!empty($params)) {
                $request = $this->generateRequest($params);
                $product->getTypeInstance()->prepareForCartAdvanced($request, $product);
                $this->itemProcessor->init($product, $request);
                $finalPrice = $product->getFinalPrice();
            } else {
                $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
            }
        }

        return $finalPrice;
    }

    /**
     * @param $params
     * @return \Magento\Framework\DataObject
     */
    protected function generateRequest($params)
    {
        $request = $this->objectFactory->create();
        $request->addData(['qty' => 1]);
        $request->addData(['related_product' => '']);
        $request->addData(['selected_configurable_option' => '']);
        if (isset($params['data'])) {
            foreach ($params['data'] as $data) {
                if (isset($data['bundle_selections'])) {
                    $request->addData(['bundle_option' => $data['bundle_selections']]);
                    break;
                }
            }
        }
        if (isset($params['product_id'])) {
            $request->addData(['product' => $params['product_id']]);
        }

        return $request;
    }
}