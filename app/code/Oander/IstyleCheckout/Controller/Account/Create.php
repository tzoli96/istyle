<?php

namespace Oander\IstyleCheckout\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Url as CustomerUrl;

/**
 * Class Create
 * @package Oander\IstyleCheckout\Controller\Account
 */
class Create extends \Magento\Checkout\Controller\Account\Create
{
    const ROUTE = 'istylecheckout/account/create';

    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    protected $resultJson;

    /**
     * @var \Oander\IstyleCheckout\Model\Order\CustomerManagement
     */
    protected $oanderOrderCustomerService;

    /**
     * @var CustomerUrl
     */
    protected $url;

    /**
     * Create constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param Session $customerSession
     * @param \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService
     * @param \Oander\IstyleCheckout\Model\Order\CustomerManagement $oanderOrderCustomerService
     * @param \Magento\Framework\Controller\Result\Json $resultJson
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService,
        \Oander\IstyleCheckout\Model\Order\CustomerManagement $oanderOrderCustomerService,
        \Magento\Framework\Controller\Result\Json $resultJson,
        \Magento\Customer\Model\Url $url
    ) {
        parent::__construct($context, $checkoutSession, $customerSession, $orderCustomerService);
        $this->oanderOrderCustomerService = $oanderOrderCustomerService;
        $this->resultJson = $resultJson;
        $this->url = $url;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute()
    {
        $password = $this->_request->getParam('password',null);
        $response = [
            'errors' => false,
            'message' => ''
        ];

        if ($this->customerSession->isLoggedIn()) {
            $response = [
                'errors' => true,
                'message' => __('Customer is already registered')
            ];
        }
        $orderId = $this->checkoutSession->getLastOrderId();
        if (!$orderId) {
            $response = [
                'errors' => true,
                'message' => __('Your session has expired')
            ];
        }
        try {
            $customer = $this->oanderOrderCustomerService->createCustmer($orderId, $password);
            $email = $this->url->getEmailConfirmationUrl($customer->getEmail());

            $response['message'] = __('You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.', $email);
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        }

        return $this->resultJson->setData($response);
    }

}