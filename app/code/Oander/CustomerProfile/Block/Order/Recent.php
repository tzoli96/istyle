<?php

namespace Oander\CustomerProfile\Block\Order;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Sales\Model\Order;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item;

class Recent extends \Magento\Sales\Block\Order\Recent
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

    /**
     * @param Context $context
     * @param CollectionFactory $orderCollectionFactory
     * @param Session $customerSession
     * @param Config $orderConfig
     * @param ImageBuilder $imageBuilder
     * @param Item $itemResourceModel
     * @param ItemFactory $quoteItemFactory
     * @param array $data
     */
    public function __construct(
        Context           $context,
        CollectionFactory $orderCollectionFactory,
        Session           $customerSession,
        Config            $orderConfig,
        ImageBuilder      $imageBuilder,
        Item              $itemResourceModel,
        ItemFactory       $quoteItemFactory,
        array             $data = []
    )
    {
        $this->imageBuilder = $imageBuilder;
        $this->itemResourceModel = $itemResourceModel;
        $this->quoteItemFactory = $quoteItemFactory;
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);

        $orders = $this->_orderCollectionFactory->create()->addAttributeToSelect(
            '*'
        )->addAttributeToFilter(
            'customer_id',
            $this->_customerSession->getCustomerId()
        )->addAttributeToFilter(
            'status',
            ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
        )->addAttributeToSort(
            'created_at',
            'desc'
        )->setPageSize(
            '2'
        )->load();
        $this->setOrders($orders);
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
            } elseif ($item->getData("parent_item_id")) {
                continue;
            }else {
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

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return $this->fetchView($this->getTemplateFile());
    }

}