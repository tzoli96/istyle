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
                if ($address && $extensionAttributes = $address->getExtensionAttributes()) {
                    if ($extensionAttributes && $data = $extensionAttributes->getPfpjRegNo()) {
                        $this->copyData($data, $address);
                    }
                }
            }
        }

        $address = $quote->getBillingAddress();
        if ($address && $extensionAttributes = $address->getExtensionAttributes()) {
            if ($extensionAttributes && $data = $extensionAttributes->getPfpjRegNo()) {
                $this->copyData($data, $address);
            }
        }

        return [$quote];
    }

    /**
     * @param string $data
     * @param AddressInterface $target
     */
    private function copyData($data, AddressInterface $target)
    {
        $target->setPfpjRegNo($data);
    }
}
