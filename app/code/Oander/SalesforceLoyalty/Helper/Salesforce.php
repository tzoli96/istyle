<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\SalesforceLoyalty\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Oander\Salesforce\Model\Endpoint\Loyalty;

class Salesforce extends AbstractHelper
{
    const REGISTRY_AVAILABLE_POINTS = "salesforceloyalty_available_points";
    const REGISTRY_HISTORY = "salesforceloyalty_history";
    /**
     * @var \Oander\Salesforce\Helper\SoapClient
     */
    private $soapClient;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Oander\SalesforceLoyalty\Helper\Config
     */
    private $configHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Oander\Salesforce\Model\Endpoint\Loyalty
     */
    private $loyaltyEndpoint;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Oander\SalesforceLoyalty\Helper\Config $configHelper
     * @param \Oander\Salesforce\Helper\SoapClient $soapClient
     * @param \Oander\Salesforce\Model\Endpoint\Loyalty $loyaltyEndpoint
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Oander\SalesforceLoyalty\Helper\Config $configHelper,
        \Oander\Salesforce\Helper\SoapClient $soapClient,
        \Oander\Salesforce\Model\Endpoint\Loyalty $loyaltyEndpoint
    ) {
        parent::__construct($context);
        $this->soapClient = $soapClient;
        $this->customerSession = $customerSession;
        $this->configHelper = $configHelper;
        $this->registry = $registry;
        $this->loyaltyEndpoint = $loyaltyEndpoint;
    }

    /**
     * @param \Magento\Customer\Model\Customer|null $customer
     * @return int
     * @throws LocalizedException
     */
    public function getCustomerAffiliatePoints($customer = null)
    {
        $customer = $this->_getCustomer($customer);
        if(is_null($this->registry->registry(self::REGISTRY_AVAILABLE_POINTS)))
            $this->registry->register(self::REGISTRY_AVAILABLE_POINTS, (int)$this->loyaltyEndpoint->GetAffiliateMembershipPointsBalance($customer->getData('sforce_maconomy_id'),substr($customer->getStore()->getCode(), 0, 2)));
        return $this->registry->registry(self::REGISTRY_AVAILABLE_POINTS);
    }


    /**
     * @param int $blockPoints
     * @param string $customer
     * @return false|string TransactionID
     * @throws LocalizedException
     */
    public function blockCustomerAffiliatePoints(int $blockPoints, string $customer = null)
    {
        $customer = $this->_getCustomer($customer);
        $transactionId = false;
        $this->registry->unregister(self::REGISTRY_AVAILABLE_POINTS);
        //$istyle_id = $customer->getData('istyle_id');
        $customer_number = $customer->getData('sforce_maconomy_id');
        if($customer_number) {
            $response = $this->loyaltyEndpoint->BlockAffiliateMembershipPoints($customer_number, substr($customer->getStore()->getCode(), 0, 2), $blockPoints);
            if(isset($response["BlockedTransactionId"]))
                $transactionId = $response["BlockedTransactionId"];
        }
        return $transactionId;
    }

    /**
     * @param string $transactionId
     * @param $customer
     * @return bool
     * @throws LocalizedException
     */
    /*public function freeCustomerAffiliatePoints($transactionId, $customer = null)
    {
        $customer = $this->_getCustomer($customer);
        $this->registry->unregister(self::REGISTRY_AVAILABLE_POINTS);
        $customer_number = $customer->getData('sforce_maconomy_id');
        if($customer_number)
            return $this->loyaltyEndpoint->UpdateAffiliateTransaction($transactionId, $customer_number, $customer->getStore()->getCode());
    }*/

    /**
     * @param \Magento\Customer\Model\Customer|null $customer
     * @return float
     * @throws LocalizedException
     */
    public function getCustomerAffiliatePointsCashConverted($customer = null)
    {
        $points = $this->getCustomerAffiliatePoints($customer);
        return $points * floatval($this->configHelper->getPointValue());
    }

    /**
     * @param \Magento\Customer\Model\Customer|null $customer
     * @param int $noOfRecords
     * @param int $pageNo
     * @return array
     * @throws LocalizedException
     */
    public function getCustomerAffiliateTransactions($customer = null, $noOfRecords = 1000000, $pageNo = 1)
    {
        $customer = $this->_getCustomer($customer);
        if(is_null($this->registry->registry(self::REGISTRY_HISTORY)))
            $this->registry->register(self::REGISTRY_HISTORY,$this->loyaltyEndpoint->GetAffiliateTransactions($customer->getData('sforce_maconomy_id'),substr($customer->getStore()->getCode(), 0, 2)));
        return $this->registry->registry(self::REGISTRY_HISTORY);
    }

    /**
     * @param \Magento\Customer\Model\Customer|null $customer
     * @return \Magento\Customer\Model\Customer
     * @throws LocalizedException
     */
    private function _getCustomer($customer = null)
    {
        if(!($customer instanceof \Magento\Customer\Model\Customer))
        {
            $customer = $this->customerSession->getCustomer();
        }
        if($customer->getId())
            return $customer;
        throw new LocalizedException(__("Only logged in user can use loyalty"));
    }

}