<?php


namespace Oney\ThreeByFour\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\Order\Payment\Repository as PaymentRepository;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Status\History as OrderStatusHistory;
use Magento\Sales\Model\OrderRepository;
use Oney\ThreeByFour\Api\Payment\ConfirmInterface;
use Oney\ThreeByFour\Logger\Logger;

class OnSaveShipment implements ObserverInterface
{
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var ConfirmInterface
     */
    protected $confirm;
    /**
     * @var OrderStatusHistory
     */
    protected $orderStatusHistory;
    /**
     * @var Transaction\Repository
     */
    protected $_transactionRepository;
    /**
     * @var InvoiceRepository
     */
    protected $_invoiceRepository;
    /**
     * @var PaymentRepository
     */
    protected $_paymentRepository;
    /**
     * @var OrderRepository
     */
    protected $_orderRepository;
    /**
     * @var Transaction\BuilderInterface
     */
    protected $transactionBuilder;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;

    public function __construct(
        ConfirmInterface $confirm,
        OrderStatusHistory $orderStatusHistory,
        Transaction\Repository $_transactionRepository,
        InvoiceRepository $_invoiceRepository,
        PaymentRepository $_paymentRepository,
        OrderRepository $_orderRepository,
        Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\DB\Transaction $transaction,
        Logger $logger
    )
    {
        $this->logger = $logger;
        $this->confirm = $confirm;
        $this->orderStatusHistory = $orderStatusHistory;
        $this->_transactionRepository = $_transactionRepository;
        $this->_invoiceRepository = $_invoiceRepository;
        $this->_paymentRepository = $_paymentRepository;
        $this->_orderRepository = $_orderRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->transaction = $transaction;
    }

    public function execute(Observer $observer)
    {
        /** @var $shipment Shipment */
        $shipment = $observer->getShipment();
        $allShipped = true;
        $order = $shipment->getOrder();
        $this->logger->info("Order Ship :: Plugin Activation");
        foreach ($order->getAllItems() as $item) {
            $this->logger->info("Order Ship :: Item To Ship ? : Not Strict : " . (($item->getQtyToShip() == 0) ? "Yes" : "No") . " Strict: " . (($item->getQtyToShip() === 0) ? "Yes" : "No"), [$item->getQtyToShip()]);
            $allShipped &= $item->getQtyToShip() == 0;
        }
        if ($allShipped && strpos($order->getPayment()->getMethod(), 'facilypay_') !== false) {
            try {
            $this->logger->info("Order Ship :: All Item Shipped => Confirmation");
            if ($this->confirm->confirm($order)) {
                $history = $this->orderStatusHistory
                    ->setStatus($order->getStatus())
                    ->setComment('Payment Captured, status : FUNDED')
                    ->setEntityName(Order::ENTITY)
                    ->setIsCustomerNotified(false)
                    ->setCreatedAt(date('Y-m-d H:i:s'));
                $order->addStatusHistory($history);
                $this->createInvoiceForOrder($order, $order->getIncrementId());
                $this->logger->info("Order Ship :: Payment Captured, status : FUNDED");
            }
            } catch(\Exception $e) {
                $this->logger->info("Order Ship :: Something went wrong while creating shipment and invoice");
            }
        }
        $this->logger->info("Order Ship :: Not All Item Shipped => Nothing Happens");
    }

    /**
     * @var Order  $order
     * @var string $order_reference
     */
    protected function createInvoiceForOrder(Order $order, $order_reference)
    {
        $this->addTransactionToOrder($order, $order_reference);
        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
        $invoice->setState(Invoice::STATE_PAID);
        $invoice->setBaseGrandTotal($order->getBaseGrandTotal());
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);
        $invoice->pay();

        $transactionSave = $this->transaction
            ->addObject($invoice)
            ->addObject($order);
        $transactionSave->save();

        $order->setTotalPaid($order->getTotalPaid());
        $order->setBaseTotalPaid($order->getBaseGrandTotal());
        $this->_invoiceRepository->save($invoice);

        $order->setStatus(Order::STATE_COMPLETE);
        $this->_orderRepository->save($order);
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
        $transaction = $this->transactionBuilder->setTransactionId($transaction_id)
            ->setOrder($order)
            ->setPayment($payment)
            ->build(Transaction::TYPE_AUTH);
        $payment->addTransactionCommentsToOrder($transaction, __('Authorized payment with amount %1', $formatedPrice));
        $payment->setParentTransactionId(null);

        $this->_paymentRepository->save($payment);
        $this->_orderRepository->save($order);
        $this->_transactionRepository->save($transaction);
    }
}
