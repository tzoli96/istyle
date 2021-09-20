<?php

namespace Oander\IstyleCheckout\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Oander\IstyleCheckout\Api\AccountManagementInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class AccountManagement implements AccountManagementInterface
{
    /**
     * @var Data
     */
    protected $jsonHelper;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * AccountManagement constructor.
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param Data $jsonHelper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        Data $jsonHelper
    )
    {
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmailAvailable($customerEmail, $websiteId = null)
    {
        $response = [];
        try {
            if ($websiteId === null) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
            }
            $customer = $this->customerRepository->get($customerEmail, $websiteId);
            $response = [
                'firstname'         => $customer->getFirstname(),
                'isEmailAvailable'  => false
            ];
            return $this->jsonHelper->jsonEncode($response);
        } catch (NoSuchEntityException $e) {
            $response['isEmailAvailable'] = true;
            return $this->jsonHelper->jsonEncode($response);
        }
    }
}
