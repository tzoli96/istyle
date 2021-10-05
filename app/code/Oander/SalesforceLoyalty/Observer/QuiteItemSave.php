<?php

namespace Oander\SalesforceLoyalty\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;
use Oander\SalesforceLoyalty\Helper\Data;
use Oander\SalesforceLoyalty\Enum\Attribute;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Model\Cart;

class QuiteItemSave implements ObserverInterface
{
    /**
     * @var Cart
     */
    protected $cart;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var Data
     */
    protected $loyaltyHelper;
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param Data $loyaltyHelper
     * @param CartRepositoryInterface $quoteRepository
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Data $loyaltyHelper,
        CartRepositoryInterface $quoteRepository,
        ManagerInterface $messageManager,
        Cart $cart
    ){
        $this->loyaltyHelper = $loyaltyHelper;
        $this->quoteRepository = $quoteRepository;
        $this->messageManager = $messageManager;
        $this->cart = $cart;
    }

    public function execute(Observer $observer)
    {
        $quote = $this->cart->getQuote();
        if($quote->getData(Attribute::LOYALTY_DISCOUNT))
        {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setLoyaltyDiscount(null);
            $quote->setLoyaltyPoint(null);
            $quote->setGrandTotal($quote->getShippingAddress()->getSubtotalInclTax());
            $this->quoteRepository->save($quote);
            $quote->save();
            $this->messageManager->addErrorMessage(__("The cart total has changed. Please set the loyalty point again!"));
        }
    }
}