<?php

namespace Oander\SalesforceLoyalty\Block\Customer\Account;

use Magento\Customer\Model\Session\Proxy;

class Points extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Oander\SalesforceLoyalty\Helper\Salesforce
     */
    private $salesforceHelper;
    /**
     * @var Proxy
     */
    private $customerSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Proxy $customerSession
     * @param \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Proxy                                            $customerSession,
        \Oander\SalesforceLoyalty\Helper\Salesforce      $salesforceHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->salesforceHelper = $salesforceHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * @return false|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLoyaltyPoints()
    {
        return $this->salesforceHelper->getCustomerAffiliatePoints($this->customerSession->getCustomer());
    }

    /**
     * @return string
     */
    public function getSalesforceId()
    {
        return $this->customerSession->getCustomer()->getData(\Oander\SalesforceReservation\Enum\Customer::SALESFORCE_ID) ?
            $this->customerSession->getCustomer()->getData(\Oander\SalesforceReservation\Enum\Customer::SALESFORCE_ID)
            : __("No SF ID");
    }
}