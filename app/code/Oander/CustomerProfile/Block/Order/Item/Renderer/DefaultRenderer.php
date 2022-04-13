<?php

namespace Oander\CustomerProfile\Block\Order\Item\Renderer;

use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;

class DefaultRenderer extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{
    /**
     * @var ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var Type
     */
    protected $bundleType;

    /**
     * @param Context $context
     * @param StringUtils $string
     * @param OptionFactory $productOptionFactory
     * @param ImageBuilder $imageBuilder
     * @param Type $bundleType
     * @param array $data
     */
    public function __construct(
        Context       $context,
        StringUtils   $string,
        OptionFactory $productOptionFactory,
        ImageBuilder  $imageBuilder,
        Type          $bundleType,
        array         $data = []
    )
    {
        $this->bundleType = $bundleType;
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
     * @param Product $product
     * @return bool
     */
    public function hasBundleParent(Product $product)
    {
        if (!$product->getTypeId() == Type::TYPE_CODE) {
            return false;
        }
        return (bool)$this->bundleType->getParentIdsByChild($product->getId());
    }
}