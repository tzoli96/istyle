<?php

namespace Oander\SalesforceLoyalty\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;
use Oander\SalesforceLoyalty\Enum\LoyaltyStatus as LoyaltyStatusEnum;
use Oander\SalesforceLoyalty\Helper\Data;
use Oander\SalesforceLoyalty\Helper\Config;
use Oander\SalesforceLoyalty\Helper\Salesforce;

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

        if ($this->configHelper->getLoyaltyServiceEnabled()) {
            if (
                ((int)$customer->getData(CustomerAttribute::LOYALTY_STATUS)) === LoyaltyStatusEnum::VALUE_NEED_SF_REGISTRATION ||
                ((int)$customer->getData(CustomerAttribute::LOYALTY_STATUS)) === LoyaltyStatusEnum::VALUE_PENDING_REGISTRATION
            ) {
                if ($this->salesForceHelper->getCustomerIsAffiliateMember()) {
                    $customerObject = $this->customerRepository->getById($customer->getId());
                    $customerObject->setData(CustomerAttribute::LOYALTY_STATUS, LoyaltyStatusEnum::VALUE_REGISTERED);
                    $customerObject->setCustomAttribute(CustomerAttribute::LOYALTY_STATUS, LoyaltyStatusEnum::VALUE_REGISTERED);
                    $this->customerRepository->save($customerObject);
                }
            }
        }
    }
}