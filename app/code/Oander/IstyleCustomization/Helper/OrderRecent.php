<?php

namespace Oander\IstyleCustomization\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Sales\Model\Order;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item;

class OrderRecent extends AbstractHelper
{
    /** @var ImageBuilder */
    private $imageBuilder;
    /**
     * @var ItemFactory
     */
    private $quoteItemFactory;
    /**
     * @var Item
     */
    private $itemResourceModel;

    public function __construct(
        Context      $context,
        ImageBuilder $imageBuilder,
        Item         $itemResourceModel,
        ItemFactory  $quoteItemFactory
    )
    {
        $this->imageBuilder = $imageBuilder;
        $this->itemResourceModel = $itemResourceModel;
        $this->quoteItemFactory = $quoteItemFactory;
        parent::__construct($context);
    }

    public function getProductImageUrl($product)
    {
        $image = $this->imageBuilder
            ->setProduct($product)
            ->setImageId('product_small_image')
            ->setAttributes([])
            ->create();

        return $image->getImageUrl();
    }

    public function getOrderStatus($order)
    {
        return ($order->getStatus() === ORDER::STATE_COMPLETE) ? __("Completed at %1", $order->getUpdateDate()) : $order->getStatusLabel();
    }

    /**
     * @param $item
     * @return bool
     */
    public function hasParentProduct($item)
    {
        return (bool)$item->getData("dropdown_main_id");
    }

    /**
     * @param $itemId
     * @return \Magento\Quote\Model\Quote\Item
     */
    public function getQuoteItemById($itemId)
    {
        $quoteItem = $this->quoteItemFactory->create();
        $this->itemResourceModel->load($quoteItem, $itemId);
        return $quoteItem;
    }

    /**
     * @param $orderItems
     * @return array
     */
    public function getOrdersItems($orderItems)
    {
        $childs = [];
        $parents = [];
        foreach ($orderItems as $item) {

            if ($item->getData("dropdown_main_id")) {
                $childs[$item->getData("dropdown_main_id")][] = $item;
            } else {
                $parents[$item->getData("quote_item_id")] = $item;
            }
        }
        if ($childs) {
            foreach ($childs as $quoteItemId => $child) {
                $parents[$quoteItemId]->setData("childs", $child);
            }
        }

        return $parents;
    }
}