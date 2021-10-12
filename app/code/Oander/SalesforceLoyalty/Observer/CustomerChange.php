<?php

namespace Oander\SalesforceLoyalty\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;

class CustomerChange implements ObserverInterface
{
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
     */
    public function __construct(
        RequestInterface            $request,
        CollectionFactory           $agreementCollectionFactory,
        StoreManagerInterface       $storeManager,
        CustomerRepositoryInterface $customerRepository
    )
    {
        $this->request = $request;
        $this->agreementCollectionFactory = $agreementCollectionFactory;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
    }

    public function execute(Observer $observer)
    {
        $agreementIds = $this->request->getParam("agreement");
        if ($agreementIds) {
            array_walk($agreementIds, function (&$value, $key) {
                $value = $key;
            });

            if ($this->checkHasAgreementId($agreementIds)) {
                $customer = $this->customerRepository->getById($observer->getEvent()->getCustomer()->getId());
                if (!$customer->getCustomAttribute(CustomerAttribute::REGISTER_TO_LOYALTY)) {
                    $customer->setCustomAttribute(CustomerAttribute::REGISTER_TO_LOYALTY, true);
                    $this->customerRepository->save($customer);
                }
            }
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