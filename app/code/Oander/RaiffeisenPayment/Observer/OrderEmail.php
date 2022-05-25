<?php
namespace Oander\RaiffeisenPayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderRepository;

class OrderEmail implements ObserverInterface
{
    const PAYMENT_METHOD_CODE = "raiffeisen";

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
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');
        if($order->getPayment()->getMethod() == self::PAYMENT_METHOD_CODE)
        {
            $order->setCanSendNewEmailFlag(false);
        }
    }


}
