<?php

namespace Oander\IstyleCustomization\Plugin\Oander\EventBasedExporter\Observer;

use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Oander\EventBasedExporter\Api\EventProcessorInterface;

class CustomerAddressSaveAfterObserver
{
    /**
     * @var EventProcessorInterface
     */
    protected $eventProcessor;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param EventProcessorInterface $eventProcessor
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        EventProcessorInterface $eventProcessor,
        CustomerInterfaceFactory $customerDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->eventProcessor = $eventProcessor;
        $this->customerDataFactory = $customerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param \Oander\EventBasedExporter\Observer\CustomerAddressSaveAfterObserver $subject
     * @param \Closure $proceedObserver
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function aroundExecute(
        \Oander\EventBasedExporter\Observer\CustomerAddressSaveAfterObserver $subject,
        \Closure $proceedObserver,
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var \Magento\Customer\Model\Address $customerAddress */
        $customerAddress = $observer->getData('customer_address');

        $customer = $customerAddress->getCustomer();
        if ($customer) {
            $customer->load($customer->getId());
            $customerDataModel = $this->getDataModel($customer, $customerAddress);
            $this->eventProcessor->processEvent('customer', $customerDataModel);
        }
    }

    /**
     * @param $customer
     * @param $newAddress
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getDataModel($customer,$newAddress)
    {
        $customerData = $customer->getData();
        $addressesData = [];
        /** @var \Magento\Customer\Model\Address $address */
        foreach ($customer->getAddresses() as $address) {
            if ($address->getEntityId() == $newAddress->getEntityId()) {
                $newAddressAttributes = array_keys($newAddress->getData());
                if ($newAddressAttributes !== null) {
                    foreach ($newAddressAttributes as $newAddressAttribute) {
                        $address->setData($newAddressAttribute, $newAddress->getData($newAddressAttribute));
                    }
                }
            }
            $addressesData[] = $address->getDataModel();
        }
        $customerDataObject = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerDataObject,
            $customerData,
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $customerDataObject->setAddresses($addressesData)
            ->setId($customer->getId());

        return $customerDataObject;
    }
}