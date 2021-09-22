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
        //TODO: Fix it on correct way #54092
        $excludeKeys = array('entity_id', 'customer_address_id', 'quote_address_id', 'region_id', 'customer_id', 'address_type');
        $oBillingAddress = $order->getBillingAddress()->getData();
        $oShippingAddress = $order->getShippingAddress()->getData();
        $oBillingAddressFiltered = array_diff_key($oBillingAddress, array_flip($excludeKeys));
        $oShippingAddressFiltered = array_diff_key($oShippingAddress, array_flip($excludeKeys));

        $addressDiff = array_diff($oBillingAddressFiltered, $oShippingAddressFiltered);
        //TODO:END

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
            if(empty($addressDiff))
            {
                if($address->getAddressType()==QuoteAddress::ADDRESS_TYPE_BILLING)
                {
                    $customerAddress->setIsDefaultShipping(true);
                    $customerData['addresses'][] = $customerAddress;
                }
            }
            else
            {
                if(strpos($order->getShippingMethod(),"warehouse_pickup") !== false)
                {
                    if($address->getAddressType()==QuoteAddress::ADDRESS_TYPE_BILLING)
                    {
                        $customerAddress->setIsDefaultShipping(true);
                        $customerData['addresses'][] = $customerAddress;
                    }
                }
                else
                {
                    $customerData['addresses'][] = $customerAddress;
                }
            }
        }

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerFactory->create(['data' => $customerData]);
        $account = $this->accountManagement->createAccount($customer, $password);
        $order->setCustomerId($account->getId());
        $this->orderRepository->save($order);
        return $account;
    }
}
