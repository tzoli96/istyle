<?php

namespace Oander\IstyleCheckout\Model\Order;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Class CustomerManagement
 * @package Oander\IstyleCheckout\Model\Order
 */
class CustomerManagement extends \Magento\Sales\Model\Order\CustomerManagement
{
    /**
     * {@inheritdoc}
     */
    public function createCustmer($orderId, $password)
    {
        $order = $this->orderRepository->get($orderId);
        if ($order->getCustomerId()) {
            throw new AlreadyExistsException(__("This order already has associated customer account"));
        }
        $customerData = $this->objectCopyService->copyFieldsetToTarget(
            'order_address',
            'to_customer',
            $order->getBillingAddress(),
            []
        );
        $addresses = $order->getAddresses();
        foreach ($addresses as $address) {
            $addressData = $this->objectCopyService->copyFieldsetToTarget(
                'order_address',
                'to_customer_address',
                $address,
                []
            );
            /** @var \Magento\Customer\Api\Data\AddressInterface $customerAddress */
            $customerAddress = $this->addressFactory->create(['data' => $addressData]);
            switch ($address->getAddressType()) {
                case QuoteAddress::ADDRESS_TYPE_BILLING:
                    $customerAddress->setIsDefaultBilling(true);
                    break;
                case QuoteAddress::ADDRESS_TYPE_SHIPPING:
                    $customerAddress->setIsDefaultShipping(true);
                    break;
            }

            if (is_string($address->getRegion())) {
                /** @var \Magento\Customer\Api\Data\RegionInterface $region */
                $region = $this->regionFactory->create();
                $region->setRegion($address->getRegion());
                $region->setRegionCode($address->getRegionCode());
                $region->setRegionId($address->getRegionId());
                $customerAddress->setRegion($region);
            }
            $customerData['addresses'][] = $customerAddress;
        }

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerFactory->create(['data' => $customerData]);
        $account = $this->accountManagement->createAccount($customer, $password);
        $order->setCustomerId($account->getId());
        $this->orderRepository->save($order);
        return $account;
    }
}
