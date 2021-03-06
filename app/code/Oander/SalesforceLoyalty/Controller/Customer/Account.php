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
use Oander\SalesforceLoyalty\Helper\Config as ConfigHelper;
use Oander\SalesforceLoyalty\Helper\Salesforce as SalesforceHelper;

class Account extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var SalesforceHelper
     */
    private $salesforceHelper;
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param SalesforceHelper $salesforceHelper
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        SalesforceHelper $salesforceHelper,
        ConfigHelper      $configHelper
    )
    {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->salesforceHelper = $salesforceHelper;
    }

    /**
     * @return ResponseInterface|ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        $page = $this->resultPageFactory->create();
        if ($this->configHelper->getLoyaltyServiceEnabled()) {
            $this->updateLoyaltyStatus();
            $loyaltyStatus = $this->customerSession->getCustomer()->getData(CustomerAttribute::LOYALTY_STATUS) ?? 0;
            $page->addHandle('salesforceloyalty_customer_account_status_' . $loyaltyStatus);
            $page->getConfig()->getTitle()->set(__('Loyalty profile info'));
        } else {
            throw new \Magento\Framework\Exception\NotFoundException(__('Parameter is incorrect.'));
        }
        return $page;
    }

    protected function updateLoyaltyStatus() {
        if(
            ((int)$this->customerSession->getCustomer()->getData(CustomerAttribute::LOYALTY_STATUS)) === LoyaltyStatusEnum::VALUE_NEED_SF_REGISTRATION ||
            ((int)$this->customerSession->getCustomer()->getData(CustomerAttribute::LOYALTY_STATUS)) === LoyaltyStatusEnum::VALUE_PENDING_REGISTRATION
        ) {
            if($this->salesforceHelper->getCustomerIsAffiliateMember()) {
                $customer = $this->customerRepository->getById($this->customerSession->getId());
                $this->customerSession->getCustomer()->setData(CustomerAttribute::LOYALTY_STATUS, LoyaltyStatusEnum::VALUE_REGISTERED);
                $customer->setCustomAttribute(CustomerAttribute::LOYALTY_STATUS, LoyaltyStatusEnum::VALUE_REGISTERED);
                $this->customerRepository->save($customer);
            }
        }
    }
}
