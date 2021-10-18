<?php

namespace Oander\IstyleCustomization\Controller\Test;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Oander\Salesforce\Helper\SoapClient;
use Magento\Customer\Model\Session;

class Index extends Action
{
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var SoapClient
     */
    private $soapClient;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;
    /**
     * @var CustomerFactory
     */
    private $_customerFactory;


    public function __construct(
        SoapClient $soapClient,
        Context $context,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerFactory $customerFactory,
        Session $customerSession
    ) {
        $this->soapClient = $soapClient;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function execute()
    {
       /* $customer =$this->_customerRepositoryInterface->getById($this->customerSession->getCustomer()->getId());
        $customer->setCustomAttribute("sforce_maconomy_id",88916);
        $customer->setCustomAttribute("istyle_id",2000000173642);
        $this->_customerRepositoryInterface->save($customer);

        var_dump($customer->getCustomAttributes());
*/
        var_dump($this->soapClient->getCustomerAffiliatePoints($this->customerSession->getCustomer()));
        die();
        // TODO: Implement execute() method.
    }
}