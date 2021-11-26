<?php

namespace Oney\ThreeByFour\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class Success extends Action
{
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param OrderSender $orderSender
     */
    public function __construct(
        Context     $context,
        Session     $checkoutSession,
        OrderSender $orderSender
    )
    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->orderSender = $orderSender;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if (!$order->getEmailSent()) {
            $this->orderSender->send($order);
            $order->addStatusToHistory(
                $order->getStatus(),
                "The order confirmation email was sent.",
                false
            );
        }
        return $this->_redirect($this->_url->getUrl('checkout/onepage/success'));
    }
}