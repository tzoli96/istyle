<?php

namespace Oander\SalesforceLoyalty\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;
use Magento\Customer\Model\Session;
use Oander\SalesforceLoyalty\Helper\Config;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

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
        if ($this->helper->isSpendingEnabled()) {
            $customer = $this->customerRepository->getById($this->customerSession->getId());
            $customer->setCustomAttribute(CustomerAttribute::REGISTER_TO_LOYALTY, true);
            $this->customerRepository->save($customer);
            $this->messageManager->addSuccessMessage(__("Your Loyalty registration request has been sent"));
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}