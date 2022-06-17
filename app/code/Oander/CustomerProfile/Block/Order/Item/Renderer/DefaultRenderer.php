<?php

namespace Oander\CustomerProfile\Block\Order\Item\Renderer;

use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Bundle\Model\Product\Type;

class DefaultRenderer extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{
    /**
     * @var ImageBuilder
     */
    protected $imageBuilder;


    /**
     * @param Context $context
     * @param StringUtils $string
     * @param OptionFactory $productOptionFactory
     * @param ImageBuilder $imageBuilder
     * @param array $data
     */
    public function __construct(
        Context       $context,
        StringUtils   $string,
        OptionFactory $productOptionFactory,
        ImageBuilder  $imageBuilder,
        array         $data = []
    )
    {
        $this->imageBuilder = $imageBuilder;
        parent::__construct($context, $string, $productOptionFactory, $data);
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getProductImageUrl(Product $product)
    {
        $image = $this->imageBuilder
            ->setProduct($product)
            ->setImageId('product_small_image')
            ->setAttributes([])
            ->create();

        return $image->getImageUrl();
    }

    /**
     * @param $item
     * @return bool
     */
    public function hasBundleParent($item)
    {
        $response = false;
        if (!$item->getTypeId() == Type::TYPE_CODE) {
            return false;
        }
        $parentItem = $item->getParentItem();
        if ($parentItem && $parentItem->getTypeId() == Type::TYPE_CODE) {
            $response = true;
        }
        return $response;
    }
}