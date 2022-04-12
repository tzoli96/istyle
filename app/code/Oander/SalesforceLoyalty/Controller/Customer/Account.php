<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\SalesforceLoyalty\Controller\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;
use Oander\SalesforceLoyalty\Enum\LoyaltyStatus as LoyaltyStatusEnum;

class Account extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Oander\SalesforceLoyalty\Helper\Salesforce
     */
    private $salesforceHelper;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper
     */
    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context);
        $this->salesforceHelper = $salesforceHelper;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return ResponseInterface|ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        $this->updateLoyaltyStatus();
        $loyaltyStatus = $this->customerSession->getCustomer()->getData(CustomerAttribute::LOYALTY_STATUS) ?? 0;
        $page = $this->resultPageFactory->create();
        $page->addHandle('salesforceloyalty_customer_account_status_' . $loyaltyStatus);
        $page->getConfig()->getTitle()->set(__('Loyalty profile info'));
        return $page;
    }

    protected function updateLoyaltyStatus() {
        if((int)$this->customerSession->getCustomer()->getData(CustomerAttribute::LOYALTY_STATUS) === LoyaltyStatusEnum::VALUE_PENDING_REGISTRATION) {
            if($this->salesforceHelper->getCustomerIsAffiliateMember()) {
                $customer = $this->customerRepository->getById($this->customerSession->getId());
                $this->customerSession->getCustomer()->setData(CustomerAttribute::LOYALTY_STATUS, LoyaltyStatusEnum::VALUE_REGISTERED);
                $customer->setCustomAttribute(CustomerAttribute::LOYALTY_STATUS, LoyaltyStatusEnum::VALUE_REGISTERED);
                $this->customerRepository->save($customer);
            }
        }
    }
}
