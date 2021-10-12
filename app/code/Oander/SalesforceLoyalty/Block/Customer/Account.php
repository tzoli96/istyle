<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\SalesforceLoyalty\Block\Customer;

use Oander\SalesforceLoyalty\Enum\CustomerAttribute;

class Account extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Oander\SalesforceLoyalty\Helper\Salesforce
     */
    private $salesforceHelper;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->salesforceHelper = $salesforceHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * @return int
     */
    public function getCustomerLoyaltyStatus()
    {
        $response = 0;
        if($this->customerSession->getCustomer()->getData(CustomerAttribute::REGISTER_TO_LOYALTY)){
            $response = 1;
        }elseif($this->customerSession->getCustomer()->getData(CustomerAttribute::REGISTRED_TO_LOYALTY))
        {
            $response = 2;
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getSalesforceId()
    {
        return $this->customerSession->getCustomer()->getData(\Oander\Salesforce\Enum\Customer::SALESFORCE_ID)?:__("No SF ID");
    }

    /**
     * @return int
     */
    public function getLoyaltyPoints()
    {
        return $this->salesforceHelper->getCustomerAffiliatePoints($this->customerSession->getCustomer());
    }

    public function getLoyaltyPointsHistory()
    {
        return $this->salesforceHelper->getCustomerAffiliateTransactions($this->customerSession->getCustomer());
    }

    /**
     * Retrieve script options encoded to json
     *
     * @return string
     */
    public function getScriptOptions()
    {
        $params = [
            'history' => $this->getLoyaltyPointsHistory(),
        ];

        return json_encode($params);
    }
}