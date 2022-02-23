<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\SalesforceLoyalty\Block\Customer;

use Oander\SalesforceLoyalty\Enum\CustomerAttribute;
use Oander\SalesforceLoyalty\Helper\Data;
use Magento\Sales\Model\OrderFactory;

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
     * @var Data
     */
    private $helperData;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper
     * @param Data $helperData
     * @param OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper,
        Data $helperData,
        OrderFactory $orderFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->salesforceHelper = $salesforceHelper;
        $this->customerSession = $customerSession;
        $this->helperData = $helperData;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @return int
     */
    public function getCustomerLoyaltyStatus()
    {
        $response = 0;
        if($this->customerSession->getCustomer()->getData(CustomerAttribute::REGISTER_TO_LOYALTY) &&
            $this->customerSession->getCustomer()->getData(CustomerAttribute::REGISTRED_TO_LOYALTY)){
            $response = 2;
        }elseif($this->customerSession->getCustomer()->getData(CustomerAttribute::REGISTER_TO_LOYALTY))
        {
            $response = 1;
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getSalesforceId()
    {
        return $this->customerSession->getCustomer()->getData(\Oander\SalesforceReservation\Enum\Customer::SALESFORCE_ID) ?
            $this->customerSession->getCustomer()->getData(\Oander\SalesforceReservation\Enum\Customer::SALESFORCE_ID)
            :__("No SF ID");
    }

    /**
     * @return int
     */
    public function getLoyaltyPoints()
    {
        return $this->salesforceHelper->getCustomerAffiliatePoints($this->customerSession->getCustomer());
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLoyaltyPointsHistory()
    {
        $result = $this->salesforceHelper->getCustomerAffiliateTransactions($this->customerSession->getCustomer());
        foreach ($result['AffiliatedTransactions'] as $index => $item){
            $order = $this->orderFactory->create()->loadByAttribute('increment_id',$item['MagentoOrderNumber']);
            $result['AffiliatedTransactions'][$index]['OrderId'] = $order->getData('entity_id');
        }
        return $result;
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

    /**
     * @return string
     */
    public function getBlockId()
    {
        return $this->helperData->getBlockId();
    }

    /**
     * @return string
     */
    public function getPromoBlockId()
    {
        return $this->helperData->getPromoBlockId();
    }
}