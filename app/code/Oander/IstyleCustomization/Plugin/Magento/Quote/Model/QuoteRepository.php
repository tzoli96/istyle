<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Quote\Model;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

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
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var array
     */
    private $addressAttributeCodes = [];

    /**
     * QuoteRepository constructor.
     * @param \Magento\Framework\Registry $registry
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->registry = $registry;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
            foreach ($this->getCustomerAddressAttributes() as $attributeCode) {
                if ($registryQuote->getShippingAddress()
                    && $registryQuote->getShippingAddress()->getData($attributeCode)
                ) {
                    $quote->getShippingAddress()->setData($attributeCode, $registryQuote->getShippingAddress()->getData($attributeCode));
                }

                if ($registryQuote->getBillingAddress()
                    && $registryQuote->getBillingAddress()->getData($attributeCode)
                ) {
                    $quote->getBillingAddress()->setData($attributeCode, $registryQuote->getBillingAddress()->getData($attributeCode));
                }
            }
        }

        return $quote;
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

    /**
     * @return array
     */
    private function getCustomerAddressAttributes()
    {
        if (empty($this->addressAttributeCodes)) {
            $searchResult = $this->attributeRepository->getList(
                \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                $this->searchCriteriaBuilder->create()
            );

            foreach ($searchResult->getItems() as $item) {
                $this->addressAttributeCodes[] = $item->getAttributeCode();
            }
        }

        return $this->addressAttributeCodes;
    }
}