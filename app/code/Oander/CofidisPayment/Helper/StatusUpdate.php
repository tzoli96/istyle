<?php

namespace Oander\CofidisPayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Store\Model\ScopeInterface;
use Oander\CofidisPayment\Enum\Config as ConfigEnum;

class StatusUpdate extends AbstractHelper
{
    const STATE_PENDING = 'new';
    const STATE_PROCESSING = 'processing';
    const STATE_CANCELED = 'canceled';
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_CANCELED = 'canceled';

    private $statusMapEN = [
        1 => "Under filling",
        2 => "In progress",
        3 => "Orderable",
        4 => "Deliverable",
        5 => "Financed",
        6 => "Technical storno",
        7 => "Before financed storno",
        8 => "After financed storn",
        9 => "Rejected",
        10 => "Manual Handling",
        11 => "Did not submit request"
    ];

    private $statusMapHU = [
        1 => "kitöltés alatt lévő igénylés",
        2 => "folyamatban lévő igénylés",
        3 => "áru rendelhető/ foglalható",
        4 => "áru kiszállítható",
        5 => "finanszírozott",
        6 => "technikai sztornó",
        7 => "finanszírozás előtt sztornó",
        8 => "finanszírozás után sztornó",
        9 => "elutasítva",
        10 => "hibalistás (manuálisan kezelendő)",
        11 => "be nem küldött státusz"
    ];

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $collectionOrderFactory;
    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $configArray;
    /**
     * @var OrderSender
     */
    private $orderSender;
    /**
     * @var \Oander\CofidisPayment\Logger\Logger
     */
    private $logger;

    public function __construct(
        Context $context,
        \Oander\CofidisPayment\Helper\Config $config,
        \Oander\CofidisPayment\Logger\Logger $logger,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionOrderFactory
    )
    {
        parent::__construct($context);
        $this->collectionOrderFactory = $collectionOrderFactory;
        $this->config = $config;
        $this->orderSender = $orderSender;
        $this->logger = $logger;
    }

    public function updateOrdersStatus()
    {
         $orderCollection = $this->collectionOrderFactory->create()
             ->addFieldToSelect('*')
             ->addFieldToFilter(\Magento\Sales\Api\Data\OrderInterface::STATUS,
                 ['in' => [self::STATUS_PENDING, self::STATUS_PROCESSING]]
             );

         $orderCollection->getSelect()
             ->join(
                 ["sop" => "sales_order_payment"],
                 'main_table.entity_id = sop.parent_id',
                 array('method')
             )
             ->where('sop.method = ?',"cofidis");
         foreach ($orderCollection as $order)
         {
             if($this->config->isEnabled($order->getStoreId()))
                $this->updateOrderStatus($order);
         }
    }

    /**
     * Update one order status by received cofidis status ID
     * @param $order \Magento\Sales\Model\Order
     */
    protected function updateOrderStatus($order){
        try {
            $this->logger->addDebug($order->getIncrementId() . ": Executing");
            $iv = $this->config->getIVCode($order->getStoreId());
            $key = $this->config->getKey($order->getStoreId());

            $order_id = $order->getIncrementId();
            $kod = base64_encode(openssl_encrypt($order_id, 'AES-256-CBC', $key, 0, $iv));

            $postdata = array(
                'shopId' => $this->config->getShopId($order->getStoreId()),
                'order_id' => $kod,
            );
            $parameters = http_build_query($postdata);

            $ch = curl_init($this->config->getStatusUrl($order->getStoreId()) . '?' . $parameters);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            $result = json_decode($result);

            $this->logger->addDebug($order->getIncrementId() . ": Response");
            $this->logger->addDebug(print_r($result, true));

            if (isset($result->loan_status)) {
                $this->setOrderStatus($order, (string)$result->loan_status);
            } else {
                $this->logger->addDebug($order->getIncrementId() . ": Order update stopped due to wrong response");
            }
        }
        catch (\Exception $e)
        {
            $this->logger->addCritical($e->getMessage());
        }
    }

    /**
     * Update one order status by received cofidis status ID
     * @param $order \Magento\Sales\Model\Order
     * @param $cofidisStatusId string
     * @return bool
     */
    protected function setOrderStatus($order, $cofidisStatusId)
    {
        if (!isset($this->statusMapEN[$cofidisStatusId])) {
            return false;
        }

        $histories = $order->getStatusHistories();
        $latestHistoryComment = array_pop($histories);
        $latestHistoryCommentComment = "";
        if($latestHistoryComment)
        {
            $latestHistoryCommentComment = $latestHistoryComment->getComment();
        }
        if ($latestHistoryCommentComment != $this->getComment($cofidisStatusId)) {
            $this->logger->addDebug($order->getIncrementId() . ": Need update by comment");
            switch ($cofidisStatusId) {
                case "1":
                case "3":
                case "4":
                    $order->addStatusHistoryComment($this->getComment($cofidisStatusId));
                    break;
                case "2":
                    if (!$order->getEmailSent()) {
                        $this->orderSender->send($order);
                        $this->logger->addDebug($order->getIncrementId() . ": New order email sent");
                    }
                    $order->addStatusHistoryComment($this->getComment($cofidisStatusId));
                    break;
                case "5":
                    if ($order->getStatus() != self::STATE_PROCESSING) {
                        $payment = $order->getPayment();
                        if ($payment->getEntityId()) {
                            $payment->registerAuthorizationNotification($order->getGrandTotal());
                            $payment->registerCaptureNotification($order->getGrandTotal());
                            $this->logger->addDebug($order->getIncrementId() . ": Payment added");
                        }
                    }
                    $order->addStatusHistoryComment($this->getComment($cofidisStatusId));
                    break;
                case "6":
                case "7":
                case "8":
                case "9":
                case "10":
                case "11":
                    if ($order->getStatus() != self::STATE_CANCELED) {
                        if ($order->canCancel()) {
                            $order->cancel();
                            $this->logger->addDebug($order->getIncrementId() . ": Order Cancelled");
                        }
                        else
                        {
                            $order->setState(self::STATE_CANCELED);
                            $order->setState(self::STATUS_CANCELED);
                        }
                    }
                    $order->addStatusHistoryComment($this->getComment($cofidisStatusId));
                    break;
            }
            $order->save();
            return true;
        }
        else{
            $this->logger->addDebug($order->getIncrementId() . ": NOT Need update by comment");
        }
        return false;
    }

    protected function getComment($cofidisStatusId)
    {
        if(!isset($this->statusMapEN[$cofidisStatusId])){
            return "";
        }
        return "Cofidis cron update:" . $cofidisStatusId . " - " . $this->statusMapEN[$cofidisStatusId];
    }
}