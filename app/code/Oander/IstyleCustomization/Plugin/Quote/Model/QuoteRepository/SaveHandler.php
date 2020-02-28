<?php

namespace Oander\IstyleCustomization\Plugin\Quote\Model\QuoteRepository;

use Magento\Quote\Model\QuoteRepository\SaveHandler as MagentoSaveHandler;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\AddressInterface;

class SaveHandler
{
    /**
     * Move dob data from address extension attributes
     * to address flat table
     *
     * @param MagentoSaveHandler $subject
     * @param CartInterface $quote
     * @return mixed
     */
    public function beforeSave(MagentoSaveHandler $subject, CartInterface $quote)
    {
        $extensionAttributes = $quote->getExtensionAttributes();
        if (!$quote->isVirtual() && $extensionAttributes && $extensionAttributes->getShippingAssignments()) {
            $shippingAssignment = current($extensionAttributes->getShippingAssignments());
            if ($shippingAssignment && $shipping = $shippingAssignment->getShipping()) {
                $address = $shipping->getAddress();
                if ($address) {
                    if ($address->getExtensionAttributes() && $address->getExtensionAttributes()->getPfpjRegNo()) {
                        $address->setPfpjRegNo($address->getExtensionAttributes()->getPfpjRegNo());
                    }
                    if ($address->getExtensionAttributes() && $address->getExtensionAttributes()->getDob()) {
                        $address->setDob(date('Y-m-d', strtotime($address->getExtensionAttributes()->getDob())));
                    } elseif ($quote->getCustomer() && $quote->getCustomer()->getDob()) {
                        $address->setDob(date('Y-m-d', strtotime($quote->getCustomer()->getDob())));
                    }
                }
            }
        }

        $address = $quote->getBillingAddress();
        if ($address) {
            if ($address->getExtensionAttributes() && $address->getExtensionAttributes()->getPfpjRegNo()) {
                $address->setPfpjRegNo($address->getExtensionAttributes()->getPfpjRegNo());
            }
            if ($address->getExtensionAttributes() && $address->getExtensionAttributes()->getDob()) {
                $address->setDob(date('Y-m-d', strtotime($address->getExtensionAttributes()->getDob())));
            } elseif ($quote->getCustomer() && $quote->getCustomer()->getDob()) {
                $address->setDob(date('Y-m-d', strtotime($quote->getCustomer()->getDob())));
            }
        }

        return [$quote];
    }
}
