<?php

namespace Oander\IstyleCustomization\Plugin\Quote\Model\Quote\Address;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Eav\Model\AttributeRepository;
use Magento\Quote\Model\Quote\Address\ToOrderAddress as MagentoToOrderAddress;
use Magento\Sales\Api\Data\OrderAddressInterface;

class ToOrderAddress
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * ToOrderAddress constructor.
     * @param AttributeRepository $attributeRepository
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        CustomerRepositoryInterface $customerRepository
    )
    {
        $this->attributeRepository = $attributeRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param  MagentoToOrderAddress $subject
     * @param  callable              $proceed
     * @param  Address               $object
     * @param  array                 $data
     * @return OrderAddressInterface
     */
    public function aroundConvert(MagentoToOrderAddress $subject, callable $proceed, Address $object, $data = [])
    {
        /** @var OrderAddressInterface $orderAddress */
        $orderAddress = $proceed($object, $data);
        $orderAddress->setPfpjRegNo($object->getPfpjRegNo());
        if($object->getPfpjRegNo())
        {
            try{
                $attribute = $this->attributeRepository->get('customer', 'pfpj_reg_no');
                $customer = $orderAddress->getOrder()->getCustomer();
                if($customer) {
                    $customer->setData($attribute->getAttributeCode(), $object->getPfpjRegNo());
                    $this->customerRepository->save($customer);
                }
            }
            catch(\Exception $e)
            {
            }
        }

        if($object->getDob()) {
            $orderAddress->setDob((string)$object->getDob());
        } elseif ($object->getQuote()->getCustomer() && $object->getQuote()->getCustomer()->getDob()) {
            $orderAddress->setDob((string)date('Y-m-d', strtotime($object->getQuote()->getCustomer()->getDob())));
        }

        return $orderAddress;
    }
}
