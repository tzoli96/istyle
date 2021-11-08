<?php

namespace Oney\ThreeByFour\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Store\Model\StoreManagerInterface;
use Oney\ThreeByFour\Api\Payment\ConfirmInterface;
use Oney\ThreeByFour\Helper\Config;
use Oney\ThreeByFour\Logger\Logger;
use Magento\Sales\Model\OrderRepository;
use \Magento\Sales\Model\Convert\Order as OrderConverter;
use Magento\Sales\Model\Order\Status\History as OrderStatusHistory;

class Callback extends Action
{
    /**
     * @var Config
     */
    protected $_helperConfig;
    /**
     * @var Logger
     */
    protected $_logger;
    /**
     * @var Order
     */
    protected $_order;
    /**
     * @var OrderStatusHistory
     */
    protected $_orderStatusHistory;
    /**
     * @var OrderRepository
     */
    protected $_orderRepository;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Order\Payment\Transaction\BuilderInterface
     */
    protected $_transactionBuilder;
    /**
     * @var Transaction\Repository
     */
    protected $_transactionRepository;
    /**
     * @var Payment\Repository
     */
    protected $_paymentRepository;
    /**
     * @var InvoiceRepository
     */
    protected $_invoiceRepository;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;
    /**
     * @var ConfirmInterface
     */
    protected $_confirmService;
    /**
     * @var OrderConverter
     */
    protected $orderConverter;
    /**
     * @var Order\ShipmentRepository
     */
    protected $shipmentRepository;

    public function __construct(
        Context $context,
        Config $helperConfig,
        Logger $logger,
        Order $order,
        StoreManagerInterface $storeManager,
        OrderRepository $orderResourceModel,
        \Magento\Framework\DB\Transaction $transaction,
        ConfirmInterface $_confirmService,
        Transaction\Repository $_transactionRepository,
        InvoiceRepository $_invoiceRepository,
        Payment\Repository $_paymentRepository,
        OrderStatusHistory $orderStatusHistory,
        OrderConverter $orderConverter,
        Order\ShipmentRepository $shipmentRepository,
        Order\Payment\Transaction\BuilderInterface $transactionBuilder
    )
    {
        $this->_logger = $logger;
        $this->_confirmService = $_confirmService;
        $this->transaction = $transaction;
        $this->_orderRepository = $orderResourceModel;
        $this->_orderStatusHistory = $orderStatusHistory;
        $this->_paymentRepository = $_paymentRepository;
        $this->_transactionRepository = $_transactionRepository;
        $this->_invoiceRepository = $_invoiceRepository;
        $this->_order = $order;
        $this->_transactionBuilder = $transactionBuilder;
        $this->storeManager = $storeManager;
        $this->_helperConfig = $helperConfig;
        parent::__construct($context);
        $this->orderConverter = $orderConverter;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $response = json_decode($this->getRequest()->getContent(), true);

        $purchase = $response['purchase'];
        $orderId = $purchase['external_reference'];

        $order = $this->_order->loadByIncrementId($orderId);

        if (!$order->getId()) {
            $this->_logger->info('Not order with this increment id');
            return;
        }
        if ($order->getState() === Order::STATE_CANCELED) {
            $this->_logger->info('Order has been canceled');
            return;
        }
        if ($order->getState() === Order::STATE_CLOSED) {
            $this->_logger->info('Order has been closed');
            return;
        }

        $this->storeManager->setCurrentStore($order->getStoreId());
        $merchant_guid = $this->_helperConfig->getGeneralConfigValue('merchant_guid');

        if (!isset($response['merchant_guid']) || $merchant_guid !== $response['merchant_guid']) {
            $this->_logger->info('Merchant guid not same as configuration');
            echo json_encode(["error" => "Merchant guid not same as configuration "]);
            return;
        }

        if (!isset($purchase['status_code'])) {
            $this->_logger->info('Need Status code to do action');
            return;
        }

        $history = $this->_orderStatusHistory
            ->setStatus($order->getStatus())
            ->setComment($purchase['status_label'] . ', status : ' . $purchase['status_code'])
            ->setEntityName(Order::ENTITY)
            ->setIsCustomerNotified(false)
            ->setCreatedAt(date('Y-m-d H:i:s'));

        $order->addStatusHistory($history);
        $this->_orderRepository->save($order);

        if (in_array($purchase['status_code'], ["REFUSED", "ABORTED", "CANCELLED"]) && !count($order->getInvoiceCollection())) {
            $history = $this->_orderStatusHistory
                ->setStatus($order->getStatus())
                ->setComment($purchase['status_label'] . ', status : ' . $purchase['status_code'])
                ->setEntityName(Order::ENTITY)
                ->setIsCustomerNotified(false)
                ->setCreatedAt(date('Y-m-d H:i:s'));
            $order->addStatusHistory($history);
            $order->cancel();
            $this->_orderRepository->save($order);
        }

        if (in_array($purchase['status_code'], ["PENDING", "FAVORABLE"]) && !count($order->getInvoiceCollection())) {
            $this->addTransactionToOrder($order, $purchase['external_reference']);
            $history = $this->_orderStatusHistory
                ->setStatus($order->getStatus())
                ->setComment($purchase['status_label'] . ', status : ' . $purchase['status_code'])
                ->setEntityName(Order::ENTITY)
                ->setIsCustomerNotified(false)
                ->setCreatedAt(date('Y-m-d H:i:s'));
            $order->addStatusHistory($history);
            if ($purchase['status_code'] === "FAVORABLE") {
                $order->setStatus(Order::STATE_PROCESSING);
                if ($this->_helperConfig->getGeneralConfigValue("automatic_ship")) {
                    $this->_logger->info('Creating Shipment');
                    $this->shipOrder($order);
                }
            } else {
                $order->setStatus(Order::STATE_PAYMENT_REVIEW);
            }
            $this->_orderRepository->save($order);
        }
        $this->getResponse()->setHttpResponseCode(200);
    }

    /**
     * @param Order  $order
     * @param string $transaction_id
     */
    protected function addTransactionToOrder(Order $order, $transaction_id)
    {
        $payment = $order->getPayment();
        $formatedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());
        $payment->setLastTransId($transaction_id)
            ->setTransactionId($transaction_id);
        $transaction = $this->_transactionBuilder->setTransactionId($transaction_id)
            ->setOrder($order)
            ->setPayment($payment)
            ->build(Transaction::TYPE_AUTH);
        $payment->addTransactionCommentsToOrder($transaction, __('Authorized payment with amount %1', $formatedPrice));
        $payment->setParentTransactionId(null);

        $this->_paymentRepository->save($payment);
        $this->_orderRepository->save($order);
        $this->_transactionRepository->save($transaction);
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    private function shipOrder($order)
    {
        if (!$order->canShip()) {
            $this->_logger->info("Oney Callback : can't create shipment");
            return false;
        }
        try {
            $shipment = $this->orderConverter->toShipment($order);
        }catch (\Exception $e) {
            $this->_logger->info("Oney Callback : Cannot convert order to shipment");
            return false;
        }
        foreach ($order->getAllItems() as $item) {
            if (!$item->getQtyToShip() || $item->getIsVirtual()) {
                continue;
            }
            $qtyShipped = $item->getQtyToShip();
            $shipmentItem = $this->orderConverter->itemToShipmentItem($item)->setQty($qtyShipped);
            $shipment->addItem($shipmentItem);
        }
        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);

        try {
            $this->shipmentRepository->save($shipment);
            $this->_orderRepository->save($shipment->getOrder());
            $this->_logger->info('Shipment Created');
            return true;
        } catch (\Exception $e) {
            $this->_logger->info("Oney Callback : something went wrong during shipment save :" . $e->getMessage());
            return false;
        }
    }
}
