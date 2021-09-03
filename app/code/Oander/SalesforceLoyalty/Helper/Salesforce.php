<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\SalesforceLoyalty\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;

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
     * @var Config
     */
    private $configHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Config $configHelper
     * @param \Oander\Salesforce\Helper\SoapClient $soapClient
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Oander\SalesforceLoyalty\Helper\Config $configHelper,
        \Oander\Salesforce\Helper\SoapClient $soapClient
    ) {
        parent::__construct($context);
        $this->soapClient = $soapClient;
        $this->customerSession = $customerSession;
        $this->configHelper = $configHelper;
        $this->registry = $registry;
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
            $this->registry->register(self::REGISTRY_AVAILABLE_POINTS, (int)$this->soapClient->getCustomerAffiliatePoints($customer));
        return $this->registry->registry(self::REGISTRY_AVAILABLE_POINTS);
    }

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
            $this->registry->register(self::REGISTRY_HISTORY, $this->soapClient->getCustomerAffiliateTransactions($customer, $noOfRecords, $pageNo));
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