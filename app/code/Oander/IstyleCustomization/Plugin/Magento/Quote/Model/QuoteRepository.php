<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Quote\Model;

/**
 * Class QuoteRepository
 * @package Oander\IstyleCustomization\Plugin\Magento\Quote\Model
 */
class QuoteRepository
{
    const QUOTE_REGISTRY = 'quote_data_';

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * QuoteRepository constructor.
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Quote\Model\QuoteRepository $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param array $sharedStoreIds
     * @return mixed|null
     */
    public function aroundGet(
        \Magento\Quote\Model\QuoteRepository $subject,
        \Closure $proceed,
        $cartId,
        array $sharedStoreIds = []
    ) {
        $quote = $proceed($cartId,$sharedStoreIds);
        if ($registryQuote = $this->registry->registry(self::QUOTE_REGISTRY . $cartId)) {
            $quote->setShippingAddress($registryQuote->getShippingAddress());
            $quote->setBillingAddress($registryQuote->getBillingAddress());
        }

        return $quote;
    }

    /**
     * @param \Magento\Quote\Model\QuoteRepository $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     */
    public function aroundSave(
        \Magento\Quote\Model\QuoteRepository $subject,
        \Closure $proceed,
        \Magento\Quote\Api\Data\CartInterface $quote
    ) {
        $proceed($quote);

        $this->registry->unregister(self::QUOTE_REGISTRY . $quote->getId());
        $this->registry->register(self::QUOTE_REGISTRY . $quote->getId(), $quote);
    }

    /**
     * @param \Magento\Quote\Model\QuoteRepository $subject
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface[]
     */
    public function beforeDelete(
        \Magento\Quote\Model\QuoteRepository $subject,
        \Magento\Quote\Api\Data\CartInterface $quote
    ) {
        $this->registry->unregister(self::QUOTE_REGISTRY . $quote->getId());
        return [$quote];
    }
}