<?php

namespace Pgc\Pgc\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderRepository;

class OrderEmail implements ObserverInterface
{
    const PAYMENT_METHOD_CODE="pgc_creditcard";
    /** @var OrderRepository */
    private $orderRepository;

    /** @var OrderSender */
    private $orderSender;

    /**
     * OrderEmail constructor.
     *
     * @param OrderRepository $orderRepository
     * @param OrderSender     $orderSender
     */
    public function __construct(OrderRepository $orderRepository, OrderSender $orderSender)
    {
        $this->orderRepository = $orderRepository;
        $this->orderSender = $orderSender;
    }

    public function execute(Observer $observer)
    {
        switch ($observer->getEvent()->getName()) {
            case 'sales_model_service_quote_submit_before': $this->postponeOrderEmail($observer); break;
            case 'checkout_onepage_controller_success_action': $this->sendOrderEmail($observer); break;
        }
    }

    private function postponeOrderEmail(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');
        if($order->getPayment()->getMethod() == self::PAYMENT_METHOD_CODE)
        {
            $order->setCanSendNewEmailFlag(false);
        }
    }

    private function sendOrderEmail(Observer $observer)
    {
        $orderIds = $observer->getEvent()->getData('order_ids');

        if (empty($orderIds)) {
            return;
        }

        $orderId = current($orderIds);

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            return;
        }

        if($order->getPayment()->getMethod() == self::PAYMENT_METHOD_CODE)
        {
            $this->orderSender->send($order);
        }

    }
}
