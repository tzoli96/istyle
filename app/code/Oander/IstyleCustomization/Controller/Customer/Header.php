<?php

namespace Oander\IstyleCustomization\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Oander\NameSwitcher\Helper\Switching as SwitchingHelper;

/**
 * Class Header
 * @package Oander\IstyleCustomization\Controller\Customer
 */
class Header extends Action
{
    const SESSION_MONOGRAM = 'monogram';

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var SwitchingHelper
     */
    protected $switchingHelper;

    /**
     * Header constructor.
     * @param Session $customerSession
     * @param Context $context
     */
    public function __construct(
        CustomerSession $customerSession,
        SwitchingHelper $switchingHelper,
        Context $context
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->switchingHelper = $switchingHelper;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->customerSession->setData(self::SESSION_MONOGRAM, false);
            return $this->getResponse()->setBody(false);
        }

        if (!$this->customerSession->getData(self::SESSION_MONOGRAM)) {
            $this->customerSession->setData(self::SESSION_MONOGRAM, $this->getCustomerMonogram());
        }

        $monogram = $this->customerSession->getData(self::SESSION_MONOGRAM);

        return $this->getResponse()->setBody($monogram);
    }

    /**
     * @return string
     */
    protected function getCustomerMonogram(): string
    {
        $customer = $this->customerSession->getCustomer();
        $firstNameChar = strtoupper(mb_substr($customer->getFirstname(), 0, 1));
        $lastNameChar = strtoupper(mb_substr($customer->getLastname(), 0, 1));

        if ($this->switchingHelper->isInverted()
            && $this->switchingHelper->isThirdPartyModulesInverted()
        ) {
            $monogram = $lastNameChar . $firstNameChar;
        } else {
            $monogram = $firstNameChar . $lastNameChar;
        }

        return $monogram;
    }
}