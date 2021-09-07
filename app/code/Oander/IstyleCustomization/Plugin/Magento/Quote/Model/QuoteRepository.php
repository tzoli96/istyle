<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Quote\Model;

use Magento\Quote\Api\Data\AddressInterface;

/**
 * Class QuoteRepository
 * @package Oander\IstyleCustomization\Plugin\Magento\Quote\Model
 */
class QuoteRepository
{
    const QUOTE_REGISTRY = 'quote_data_';

    const REGISTRY_ADDRESS_FIELDS = [
        AddressInterface::KEY_EMAIL,
        AddressInterface::KEY_COUNTRY_ID,
        AddressInterface::KEY_ID,
        AddressInterface::KEY_REGION_ID,
        AddressInterface::KEY_REGION_CODE,
        AddressInterface::KEY_REGION,
        AddressInterface::KEY_CUSTOMER_ID,
        AddressInterface::KEY_STREET,
        AddressInterface::KEY_COMPANY,
        AddressInterface::KEY_TELEPHONE,
        AddressInterface::KEY_FAX,
        AddressInterface::KEY_POSTCODE,
        AddressInterface::KEY_CITY,
        AddressInterface::KEY_FIRSTNAME,
        AddressInterface::KEY_LASTNAME,
        AddressInterface::KEY_MIDDLENAME,
        AddressInterface::KEY_PREFIX,
        AddressInterface::KEY_SUFFIX,
        AddressInterface::KEY_VAT_ID,
        AddressInterface::SAME_AS_BILLING,
        AddressInterface::CUSTOMER_ADDRESS_ID,
        AddressInterface::SAVE_IN_ADDRESS_BOOK,
        \Oander\IstyleCustomization\Observer\OrderExportAfter::PFPJ_REG_NO_ATTRIBUTE_CODE,
        \Oander\IstyleCustomization\Observer\OrderExportAfter::COMPANY_REGISTRATION_NUMBER_ATTRIBUTE_CODE
    ];

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
            foreach (self::REGISTRY_ADDRESS_FIELDS as $field) {
                if ($registryQuote->getShippingAddress()
                    && $registryQuote->getShippingAddress()->getData($field)
                ) {
                    $quote->getShippingAddress()->setData($field, $registryQuote->getShippingAddress()->getData($field));
                }

                if ($registryQuote->getBillingAddress()
                    && $registryQuote->getBillingAddress()->getData($field)
                ) {
                    $quote->getBillingAddress()->setData($field, $registryQuote->getBillingAddress()->getData($field));
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
}