<?php

namespace Oander\SalesforceLoyalty\Plugin\Magento\Checkout\Block\Onepage;

use Innobyte\CheckoutSuccess\Block\Onepage\Success as extendedSuccess;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session\Proxy;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;
use Oander\SalesforceLoyalty\Helper\Data;

class Success extends extendedSuccess
{
    /**
     * @var Proxy
     */
    protected $customerSession;
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Proxy $customerSession
     * @param array $data
     */
    public function __construct(
        Context                             $context,
        Session                             $checkoutSession,
        \Magento\Sales\Model\Order\Config   $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        Proxy                               $customerSession,
        Data                                $helperData,
        array                               $data = []
    )
    {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
        $this->customerSession = $customerSession;
        $this->helperData = $helperData;
    }

    /**
     * @return Proxy
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @return int
     */
    public function getCustomerLoyaltyStatus()
    {
        $response = 0;
        if ($this->customerSession->getCustomer()->getData(CustomerAttribute::REGISTER_TO_LOYALTY) &&
            $this->customerSession->getCustomer()->getData(CustomerAttribute::REGISTERED_TO_LOYALTY)) {
            $response = 2;
        } elseif ($this->customerSession->getCustomer()->getData(CustomerAttribute::REGISTER_TO_LOYALTY)) {
            $response = 1;
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getBlockId()
    {
        return $this->helperData->getBlockId();
    }
}