<?php

namespace Oander\SalesforceLoyalty\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;
use Oander\SalesforceLoyalty\Enum\LoyaltyStatus as LoyaltyStatusEnum;
use Oander\SalesforceLoyalty\Helper\Config;

class LoyaltyRegistrationProcess extends Action
{
    /**
     * @var CollectionFactory
     */
    private $agreementCollection;
    /**
     * @var Config
     */
    private $helper;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Session                     $customerSession,
        Config                      $helper,
        CollectionFactory           $agreementCollection,
        Context                     $context
    )
    {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->agreementCollection = $agreementCollection;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->helper->getLoyaltyServiceEnabled()) {
            $customer = $this->customerRepository->getById($this->customerSession->getId());
            if(($customer->getCustomAttribute(CustomerAttribute::LOYALTY_STATUS) ? (int)$customer->getCustomAttribute(CustomerAttribute::LOYALTY_STATUS)->getValue() : LoyaltyStatusEnum::VALUE_NOT_REGISTERED) !== LoyaltyStatusEnum::VALUE_REGISTERED) {
                $customer->setCustomAttribute(CustomerAttribute::LOYALTY_STATUS, LoyaltyStatusEnum::VALUE_NEED_SF_REGISTRATION);
                $this->customerRepository->save($customer);
                $this->messageManager->addSuccessMessage(__("Your Loyalty registration request has been sent"));
            }
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}