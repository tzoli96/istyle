<?php
namespace Oander\HelloBankPayment\Model;

use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Oander\HelloBankPayment\Enum\Request;

class HelloBank
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        UrlInterface $url
    ) {
        $this->orderRepository = $orderRepository;
        $this->url = $url;
    }

    /**
     * @return string
     */
    private function getRedirectUrl()
    {
        return $this->url->getUrl(Request::PAYMENT_PROCCESSING_ACTION);
    }

    /**
     * @param Order $order
     * @return void
     */
    public function handleStatus(Order $order)
    {
        $order->setStatus(Order::STATE_PROCESSING);
        $order->addStatusToHistory(
            $order->getStatus(),
            'Order processing by HelloBank'
        );
        $this->orderRepository->save($order);
    }

}