<?php

namespace Oney\ThreeByFour\Plugin\Sales\Model\Order;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Order\CreditmemoRepository;
use Magento\Sales\Model\OrderRepository;
use Oney\ThreeByFour\Api\Payment\CancelInterface;

class CreditmemoRepositoryPlugin
{
    /**
     * @var CancelInterface
     */
    private $_cancelService;
    /**
     * @var OrderRepository
     */
    private $_orderRepository;

    public function __construct(
        CancelInterface $cancelService,
        OrderRepository $orderRepository
    )
    {
        $this->_cancelService = $cancelService;
        $this->_orderRepository = $orderRepository;
    }

    /**
     * @param CreditmemoRepository $subject
     * @param CreditmemoInterface  $result
     */
    public function afterSave(CreditmemoRepository $subject, $result)
    {
        $this->_cancelService
            ->setCancellationAmount($result->getGrandTotal())
            ->setCancellationReasonCode(0)
            ->cancel($this->_orderRepository->get($result->getOrderId()));
    }
}
