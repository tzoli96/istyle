<?php
namespace Oander\HelloBankPayment\Model;

use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;

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

    const COMMON_URL = 'hellobank/payment/processing/';

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
        return $this->url->getUrl(self::COMMON_URL);
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function handleStatus($order)
    {
        $order->setStatus(Order::STATE_PROCESSING);
        $order->addStatusToHistory(
            $order->getStatus(),
            'Order processing by HelloBank'
        );
        $this->orderRepository->save($order);
        return false;
    }

}