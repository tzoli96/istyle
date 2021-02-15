<?php
namespace Oander\HelloBankPayment\Controller\Payment;

use Oander\HelloBankPayment\Gateway\Config\ConfigValueHandler;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

class Redirect extends Action
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ConfigValueHandler
     */
    private $config;

    /**
     * Redirect constructor.
     *
     * @param Context         $context
     * @param CheckoutSession $checkoutSession
     * @param ConfigValueHandler          $config
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        ConfigValueHandler $config
    ) {
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
            $resultRedirect->setPath('hellobank/payment/processing');
        }

        return $resultRedirect;
    }
}
