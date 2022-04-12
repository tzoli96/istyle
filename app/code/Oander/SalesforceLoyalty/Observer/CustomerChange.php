<?php

namespace Oander\SalesforceLoyalty\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;
use Oander\SalesforceLoyalty\Helper\Config;
use Oander\SalesforceLoyalty\Enum\LoyaltyStatus as LoyaltyStatusEnum;

class CustomerChange implements ObserverInterface
{
    /**
     * @var Config
     */
    private $helper;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var CollectionFactory
     */
    private $agreementCollectionFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param RequestInterface $request
     * @param CollectionFactory $agreementCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param Config $helper
     */
    public function __construct(
        RequestInterface            $request,
        CollectionFactory           $agreementCollectionFactory,
        StoreManagerInterface       $storeManager,
        CustomerRepositoryInterface $customerRepository,
        Config                      $helper
    )
    {
        $this->request = $request;
        $this->agreementCollectionFactory = $agreementCollectionFactory;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        $agreementIds = $this->request->getParam("agreement");
        if ($this->helper->getLoyaltyServiceEnabled() && $agreementIds) {
            if ($this->helper->getRegistrationTermType()) {
                array_walk($agreementIds, function (&$value, $key) {
                    $value = $key;
                });

                if ($this->checkHasAgreementId($agreementIds)) {
                    $this->saveLoyaltyAttribute($observer->getEvent()->getCustomer()->getId());
                }

            } else {
                $this->saveLoyaltyAttribute($observer->getEvent()->getCustomer()->getId());
            }
        }
    }

    /**
     * @param $customerId
     * @return void
     */
    private function saveLoyaltyAttribute($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        if (((int)$customer->getCustomAttribute(CustomerAttribute::LOYALTY_STATUS)) === LoyaltyStatusEnum::VALUE_NOT_REGISTERED) {
            $customer->setCustomAttribute(CustomerAttribute::LOYALTY_STATUS, LoyaltyStatusEnum::VALUE_NEED_SF_REGISTRATION);
            $this->customerRepository->save($customer);
        }
    }

    /**
     * @param array $agreementIds
     * @return bool
     */
    private function checkHasAgreementId(array $agreementIds): bool
    {
        $response = false;
        $agreements = $this->agreementCollectionFactory->create();
        $agreements->addStoreFilter($this->storeManager->getStore()->getId());
        $agreements->addFieldToFilter('is_active', 1);
        $agreements->addFieldToFilter('agreement_type', [
            ['eq' => 'loyalty']
        ]);
        foreach ($agreements as $agreement) {
            if (in_array($agreement->getId(), $agreementIds)) {
                $response = true;
                break;
            }
        }

        return $response;
    }
}