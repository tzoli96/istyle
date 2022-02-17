<?php

namespace Oander\CustomerProfile\Block\Order\Item\Renderer;

use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Block\Product\ImageBuilder;

class DefaultRenderer extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{
    /**
     * @var ImageBuilder
     */
    protected $imageBuilder;

    public function __construct(
        Context $context,
        StringUtils $string,
        OptionFactory $productOptionFactory,
        ImageBuilder $imageBuilder,
        array $data = []
    ){
        $this->imageBuilder = $imageBuilder;
        parent::__construct($context, $string, $productOptionFactory, $data);
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductImageUrl($product)
    {
        $image = $this->imageBuilder
            ->setProduct($product)
            ->setImageId('product_small_image')
            ->setAttributes([])
            ->create();

        return $image->getImageUrl();
    }
}