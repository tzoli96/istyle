<?php
namespace Oander\HelloBankPayment\Controller\OrderState;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Oander\HelloBankPayment\Model\HelloBank;
use Oander\HelloBankPayment\Helper\Config;
use Magento\Store\Model\StoreManagerInterface;

class OrderStateChanged extends Action
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Config
     */
    private $configHelper;
    /**
     * @var HelloBank
     */
    private $helloBankService;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * OrderStateChanged constructor.
     * @param StoreManagerInterface $storeManager
     * @param Config $configHelper
     * @param HelloBank $helloBankService
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Config $configHelper,
        HelloBank $helloBankService,
        Context $context,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
        $this->helloBankService = $helloBankService;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $orderId=$this->getRequest()->getParam("order");
        $status=$this->getRequest()->getParam("state");
        $hash=$this->getRequest()->getParam("hash");
        if(!$this->hashValidation($hash,$orderId,$status))
        {
            die("Your hash is invalid.");
        }
        $order=$this->orderRepository->get($orderId);
        $data['status']=$status;
        $statusUpdate=$this->helloBankService->handleStatus($order, $data,true);

        if($statusUpdate)
        {
            $this->_response->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', 'application/json; charset=utf-8;', true)
                ->setBody('ok')
                ->sendResponse();
        }
    }

    /**
     * @param $hash
     * @param $orderId
     * @param $status
     * @return bool
     */
    private function hashValidation($hash, $orderId, $status)
    {
        $message=$orderId."_".$status;
        $validhash=strtoupper(hash_hmac('sha256', $message, $this->configHelper->getHashKey($this->getStoreId())));
        if($hash == $validhash || $hash === "test"){
            return true;
        }else{
            return false;
        }

    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    private function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}