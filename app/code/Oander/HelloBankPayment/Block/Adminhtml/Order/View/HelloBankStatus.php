<?php
namespace Oander\HelloBankPayment\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Oander\HelloBankPayment\Gateway\Config;

class HelloBankStatus extends Template
{

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    private $orderId;

    public function __construct(
        Template\Context $context,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return OrderInterface
     */
    private function getOrder()
    {
        return $this->orderRepository->get($this->getRequest()->getParam('order_id'));
    }

    /**
     * @return int|null
     */
    public function hasHelloBank()
    {
      return $this->getOrder()->getHelloBankStatus();
    }

    /**
     * @return string
     */
    public function getStatusValue()
    {
        return Config::$hellobankOrderStatus[$this->hasHelloBank()];
    }
}