<?php

namespace Oander\SalesforceLoyalty\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;
use Oander\SalesforceLoyalty\Helper\Data;
use Oander\SalesforceLoyalty\Helper\Config;
use Oander\SalesforceLoyalty\Helper\Salesforce;
use Magento\Customer\Api\CustomerRepositoryInterface;

class CustomerLogin implements ObserverInterface
{
    /**
     * @var Config
     */
    private $configHelper;
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Salesforce
     */
    private $salesForceHelper;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        Data                        $helper,
        Config                      $configHelper,
        Salesforce                  $salesForceHelper,
        CustomerRepositoryInterface $customerRepository
    )
    {
        $this->helper = $helper;
        $this->configHelper = $configHelper;
        $this->salesForceHelper = $salesForceHelper;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        if ($this->configHelper->getLoyaltyServiceEnabled() && !$this->helper->getCustomerLoyaltyStatus()) {
            if ($this->salesForceHelper->getCustomerIsAffiliateMember()) {
                $customer = $this->customerRepository->getById($customer->getId());
                switch ($this->helper->getCustomerLoyaltyStatus()) {
                    case 2:
                        $customer->setCustomAttribute(CustomerAttribute::REGISTER_TO_LOYALTY, true);
                        $customer->setCustomAttribute(CustomerAttribute::REGISTRED_TO_LOYALTY, true);
                        break;
                    case 1:
                        $customer->setCustomAttribute(CustomerAttribute::REGISTER_TO_LOYALTY, true);
                        break;
                }
 
                $this->customerRepository->save($customer);
            }
        }
    }
}