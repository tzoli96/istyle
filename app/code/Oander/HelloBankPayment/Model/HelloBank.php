<?php
namespace Oander\HelloBankPayment\Model;

use Magento\Framework\DB\Transaction as DBTransaction;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Oander\HelloBankPayment\Enum\Request;
use Oander\HelloBankPayment\Gateway\Config;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\DB\TransactionFactory;
use Oander\HelloBankPayment\Enum\Attribute;

class HelloBank
{

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var BuilderInterface
     */
    private $transactionBuilder;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        BuilderInterface $transactionBuilder,
        OrderRepositoryInterface $orderRepository,
        UrlInterface $url,
        OrderSender $orderSender,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory
    ) {
        $this->transactionBuilder = $transactionBuilder;
        $this->orderRepository = $orderRepository;
        $this->url = $url;
        $this->orderSender = $orderSender;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * @return string
     */
    private function getRedirectUrl()
    {
        return $this->url->getUrl(Request::PAYMENT_PROCCESSING_ACTION);
    }

    public function handle(Order $order, $paymentData = null, $urlType = false)
    {
            switch ($urlType)
            {
                case Config::HELLOBANK_REPONSE_TYPE_OK:
                    $this->handleStatus($order,$paymentData['status']);

                    $this->postActions($order);
                    break;

                case Config::HELLOBANK_REPONSE_TYPE_KO:
                    $this->setHelloBankStatus($order, $paymentData['status']);
                    $this->orderSender->send($order);
                default:
            }
    }

    /**
     * @param $order
     * @param $status
     * @return void
     */
    public function setHelloBankStatus($order, $status)
    {
        $order->setData("hello_bank_status", $status);
        $order->addStatusToHistory(
            $order->getStatus(),
            "HelloBank Status is :".Config::$hellobankOrderStatus[$status]
        );
        $this->orderRepository->save($order);
    }

    /**
     * @param Order $order
     * @param null $paymentData
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handleStatus(Order $order, $paymentData = null)
    {
        switch ($paymentData['status']) {
            case CONFIG::HELLOBANK_RESPONSE_STATE_APPROVED:

                $this->setHelloBankStatus($order, $paymentData['status']);
                $order->setStatus(Order::STATE_PROCESSING);

                // Transaction Id Generate
                $payment=$order->getPayment();
                /** @var $payment Payment */
                $transactionId=(isset($paymentData['id'])) ? $paymentData['id'] : random_int(0, 10000);
                $payment->setTransactionId($transactionId);
                $payment->setIsTransactionClosed(true);

                //Invoce Generate
                        /** @var Invoice $invoice */
                        $invoice = $this->invoiceService->prepareInvoice($order);
                        /** @noinspection PhpUndefinedMethodInspection */
                        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
                        $invoice->register();

                        /** @var DBTransaction $transactionSave */
                        $transactionSave = $this->transactionFactory->create()
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());

                        $transactionSave->save();

                $this->orderSender->send($order);
                $this->orderRepository->save($order);

            case CONFIG::HELLOBANK_RESPONSE_STATE_FURTHER_REVIEW:

                $this->setHelloBankStatus($order, $paymentData['status']);
                $this->orderSender->send($order);

            case CONFIG::HELLOBANK_RESPONSE_STATE_PRE_APPROVAL:
            case CONFIG::HELLOBANK_RESPONSE_STATE_CANCELLED:
            case CONFIG::HELLOBANK_RESPONSE_STATE_REJECTED:
            case CONFIG::HELLOBANK_RESPONSE_STATE_READY_FOR_SHIPPING:
            case CONFIG::HELLOBANK_RESPONSE_STATE_WAITING_FOR_DELIVERY:
            default:
        }
        return true;
    }

    public function postActions($order = null, $paymentData = null)
    {
        //numaut = transcationID ?
        $transactionId=$paymentData['id'];
        try {
            $payment = $order->getPayment();
            $payment->setLastTransId($transactionId);
            $payment->setTransactionId($transactionId);
            $payment->setAdditionalInformation(
                [Transaction::RAW_DETAILS => (array) $paymentData]
            );
            $formatedPrice = $order->getBaseCurrency()->formatTxt(
                $order->getGrandTotal()
            );

            $message = __('The authorized amount is %1.', $formatedPrice);
            $trans = $this->transactionBuilder;
            $transaction = $trans->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transactionId)
                ->setAdditionalInformation(
                    [Transaction::RAW_DETAILS => $transactionId]
                )
                ->setFailSafe(true)
                ->build(Transaction::TYPE_CAPTURE);

            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $payment->setParentTransactionId(null);
            $payment->save();
            $order->save();
            $transaction->save()->getTransactionId();
            $this->createInvoce($order);
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * @param $order
     * @return void
     */
    private function createInvoce($order)
    {
        /** @var Invoice $invoice */
        $invoice = $this->invoiceService->prepareInvoice($order);
        /** @noinspection PhpUndefinedMethodInspection */
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
        $invoice->register();

        /** @var DBTransaction $transactionSave */
        $transactionSave = $this->transactionFactory->create()
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $transactionSave->save();
    }

}