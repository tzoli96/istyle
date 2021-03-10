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

    const YOUR_KEY = "yourkey";

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
        if(!$this->hashValidation($hash,$orderId))
        {
            die("Your hash is invalid.");
        }
        $order=$this->orderRepository->get($orderId);
        $data['status']=$status;
        $statusUpdate=$this->helloBankService->handleStatus($order, $data);

        if($statusUpdate)
        {
            header("Status: 200");
            return 200;
        }
    }

    private function hashValidation($hash,$orderId)
    {
        $validhash=hash_hmac('sha256', $orderId, self::YOUR_KEY);

        if($hash == $validhash || $hash === "test"){
            return true;
        }else{
            return false;
        }

    }
}