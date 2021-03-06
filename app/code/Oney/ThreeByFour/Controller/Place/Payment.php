<?php

namespace Oney\ThreeByFour\Controller\Place;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class Payment extends Action
{
    /**
     * @var Session
     */
    protected $_checkoutSession;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Payment constructor.
     *
     * @param Context                  $context
     * @param Session                  $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository
    )
    {
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $returned_url = json_decode($this->_checkoutSession->getData('oney_response', true)[0], true);
            $order = $this->_checkoutSession->getLastRealOrder()
                ->setStatus(Order::STATE_PENDING_PAYMENT);
            $this->orderRepository->save($order);
            $this->_checkoutSession->setOneyResponse('oney_reponse', null);
            $this->_redirect($returned_url['returned_url']);
        } catch (\Exception $e) {
            $this->_redirect($this->_url->getUrl('checkout/onepage/failure'));
        }
    }
}
