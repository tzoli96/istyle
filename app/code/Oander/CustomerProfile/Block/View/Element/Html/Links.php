<?php

namespace Oander\CustomerProfile\Block\View\Element\Html;

use Magento\Customer\Model\Customer;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session\Proxy;

class Links extends \Magento\Framework\View\Element\Html\Links
{
    /**
     * @var Proxy
     */
    private $customerSession;
    /**
     * @var \Oander\NameSwitcher\Helper\Switching
     */
    private $switchingHelper;

    /**
     * @param Proxy $customerSession
     * @param Template\Context $context
     * @param \Oander\NameSwitcher\Helper\Switching $switchingHelper
     * @param array $data
     */
    public function __construct(
        Proxy            $customerSession,
        Template\Context $context,
        \Oander\NameSwitcher\Helper\Switching $switchingHelper,
        array            $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->switchingHelper = $switchingHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return Customer
     */
    private function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * @return string
     */
    public function getMonogram()
    {
        $firstNameChar = substr($this->getCustomer()->getData("firstname"), 0, 1);
        $lastNameChar = substr($this->getCustomer()->getData("lastname"), 0, 1);
        return strtoupper($firstNameChar . $lastNameChar);
    }

    /**
     * @return string
     */
    public function getCustomerFullName()
    {
        if($this->switchingHelper->isInverted())
        {
            return $this->getCustomer()->getLastname() . " " . $this->getCustomer()->getFirstname();
        }
        return $this->getCustomer()->getName();
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->getCustomer()->getEmail();
    }
}