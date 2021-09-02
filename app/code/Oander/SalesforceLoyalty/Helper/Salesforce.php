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
    /**
     * @var \Oander\Salesforce\Helper\SoapClient
     */
    private $soapClient;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Oander\Salesforce\Helper\SoapClient $soapClient
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Oander\Salesforce\Helper\SoapClient $soapClient
    ) {
        parent::__construct($context);
        $this->soapClient = $soapClient;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Customer\Model\Customer|null $customer
     * @return float
     * @throws LocalizedException
     */
    public function getCustomerAffiliatePoints($customer = null)
    {
        if(!($customer instanceof \Magento\Customer\Model\Customer))
        {
            $customer = $this->customerSession->getCustomer();
        }
        if($customer->getId()) {
            return $this->soapClient->getCustomerAffiliatePoints($customer);
        }
        throw new LocalizedException(__("Only logged in user can use loyalty"));
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
        if(!($customer instanceof \Magento\Customer\Model\Customer))
        {
            $customer = $this->customerSession->getCustomer();
        }
        if($customer->getId())
        {
            return $this->soapClient->getCustomerAffiliateTransactions($customer, $noOfRecords, $pageNo);
        }
        throw new LocalizedException(__("Only logged in user can use loyalty"));
    }

}