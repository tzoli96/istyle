<?php

namespace Oander\RaiffeisenPayment\Controller\Payment;

use Oander\RaiffeisenPayment\Gateway\Config\ConfigValueHandler;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class Redirect extends Action
{
    /**
     * @var RequestBuild
     */
    private $requestBuild;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ConfigValueHandler
     */
    private $config;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;


    /**
     * Redirect constructor.
     *
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param ConfigValueHandler $config
     */
    public function __construct(
        JsonFactory        $_resultJsonFactory,
        PageFactory        $_resultPageFactory,
        Context            $context,
        CheckoutSession    $checkoutSession,
        ConfigValueHandler $config
    )
    {
        $this->_resultJsonFactory = $_resultJsonFactory;
        $this->_resultPageFactory = $_resultPageFactory;
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
    }

    /**
     * @return RedirectResult
     */
    public function execute()
    {
        /** @var RedirectResult $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('checkout/cart/index');

        /** @var Order $order */
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order instanceof Order) {
            /** @var Payment $payment */
            $payment = $order->getPayment();
            $resultRedirect->setPath('checkout/onepage/success');
        }
        return $resultRedirect;
    }
}
