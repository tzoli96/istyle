<?php
namespace Oander\HelloBankPayment\Controller\OrderState;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use \Magento\Sales\Api\OrderRepositoryInterface;
use  Oander\HelloBankPayment\Model\HelloBank;

class OrderStateChanged extends Action
{
    /**
     * @var HelloBank
     */
    private $helloBankService;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        HelloBank $helloBankService,
        Context $context,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->helloBankService = $helloBankService;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }


    public function execute()
    {
        $orderId=$this->getRequest()->getParam("order");
        $status=$this->getRequest()->getParam("state");
        $hash=$this->getRequest()->getParam("hash");
        if(!$this->hashValidation($hash))
        {
           return false;
        }
        $order=$this->orderRepository->get($orderId);
        $this->helloBankService->setHelloBankStatus($order, $status);

        return true;
    }

    private function hashValidation($hash)
    {
        return true;
    }
}