<?php

namespace Oander\CustomerProfile\Block\Order\Item\Renderer;

use Magento\Bundle\Block\Sales\Order\Items\Renderer;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditMemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;


class BundleDefaultRenderer extends Renderer
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
        Context $context,
        StringUtils $string,
        OptionFactory $productOptionFactory,
        ImageBuilder $imageBuilder,
        array $data = []
    ) {
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

    /**
     * Return item unit price html
     *
     * @param OrderItem|InvoiceItem|CreditmemoItem $item child item in case of bundle product
     * @return string
     */
    public function getItemPriceHtml($item = null)
    {
        $block = $this->getLayout()->getBlock('oander_item_unit_price');
        if (!$item) {
            $item = $this->getItem();
        }
        $block->setItem($item);
        return $block->toHtml();
    }
}