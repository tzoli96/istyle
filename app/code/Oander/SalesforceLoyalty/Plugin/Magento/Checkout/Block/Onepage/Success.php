<?php

namespace Oander\SalesforceLoyalty\Plugin\Magento\Checkout\Block\Onepage;

use Magento\Checkout\Block\Onepage\Success as extendedSuccess;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session\Proxy;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;

class Success extends extendedSuccess
{
    /**
     * @var Proxy
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Proxy $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        \Magento\Sales\Model\Order\Config  $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        Proxy $customerSession,
        array $data = []
    ){
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
        $this->customerSession = $customerSession;
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
        if($this->customerSession->getCustomer()->getData(CustomerAttribute::REGISTER_TO_LOYALTY) &&
            $this->customerSession->getCustomer()->getData(CustomerAttribute::REGISTRED_TO_LOYALTY)){
            $response = 2;
        }elseif($this->customerSession->getCustomer()->getData(CustomerAttribute::REGISTER_TO_LOYALTY))
        {
            $response = 1;
        }
        return $response;
    }
}