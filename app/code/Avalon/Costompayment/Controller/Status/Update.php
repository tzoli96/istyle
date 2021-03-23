<?php

namespace Avalon\Costompayment\Controller\Status;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;

/**
 * Class Update
 * @package Avalon\Costompayment\Controller\Status
 */
class Update extends \Magento\Framework\App\Action\Action
{
    const ACTION_KEY = 'costompayment/status/update';
    const PRIVATE_KEY = 'keys/private_bg.key';

    const PARAM_ORDER_ID = 'orderId';
    const PARAM_STATUS_ID = 'statusId';
    const PARAM_MESSAGE = 'reason';

    const STATUS_APPROVED = [self::PARAM_STATUS_ID => 1 , self::PARAM_MESSAGE => ['']];
    const STATUS_CANCEL = [self::PARAM_STATUS_ID => 0 , self::PARAM_MESSAGE => ['']];
    const STATUS_PENDING = [self::PARAM_STATUS_ID => 2 , self::PARAM_MESSAGE => ['']];
    const STATUS_REJECTED = [self::PARAM_STATUS_ID => 0 , self::PARAM_MESSAGE => ['Respins Biroul de Credit','Criterii eligibilitate']];

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Avalon\Costompayment\Helper\Data
     */
    protected $tbiHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
     */
    private $transactionBuilder;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    private $orderConfig;

    /**
     * Update constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Avalon\Costompayment\Helper\Data $tbiHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param Order\Config $orderConfig
     * @param Transaction\BuilderInterface $transactionBuilder
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Avalon\Costompayment\Helper\Data $tbiHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
    ) {
        $this->pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->order = $order;
        $this->tbiHelper = $tbiHelper;
        $this->imageHelper = $imageHelper;
        $this->filesystem = $filesystem;
        $this->orderConfig = $orderConfig;
        $this->transactionBuilder =  $transactionBuilder;
        return parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $requestContent = $this->getRequest()->getContent();
            $this->tbiHelper->addLog('UPDATE: ', var_export($requestContent, true));

            if ($this->isJson($requestContent)) {

                $this->tbiHelper->addLog('isJson!');
                $orderInfo = json_decode($requestContent, true);
            } else {

                $this->tbiHelper->addLog('NOT Json!');

                if (!$privateKey = openssl_pkey_get_private(file_get_contents($this->filesystem
                        ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
                        ->getAbsolutePath() . self::PRIVATE_KEY))
                ) {
                    $this->tbiHelper->addLog('Private Key failed!');
                    throw new \Exception('Private Key failed');
                }

                $this->tbiHelper->addLog('Private Key OK!');
                $a_key = openssl_pkey_get_details($privateKey);
                $chunkSize = ceil($a_key['bits'] / 8);
                $decryptedRequestContent = '';

                if ((strpos($requestContent, 'order_data=')) !== FALSE) {
                    $requestData = explode('order_data=', $requestContent);
                    $requestContent = $requestData[1];
                }
                //$encrypted = base64_decode($requestContent);
                $encrypted = $requestContent;


                $this->tbiHelper->addLog('$encrypted:', $encrypted);

                while ($encrypted) {
                    $chunk = substr($encrypted, 0, $chunkSize);
                    $encrypted = substr($encrypted, $chunkSize);
                    $decrypted = '';
                    if (!openssl_private_decrypt($chunk, $decrypted, $privateKey)) {
                        $this->tbiHelper->addLog('Failed to decrypt data');
                        throw new \Exception('Failed to decrypt data');
                    }
                    $decryptedRequestContent .= $decrypted;
                }
                openssl_free_key($privateKey);
                $this->tbiHelper->addLog('$decryptedRequestContent: '.$decryptedRequestContent);

                $orderInfo = json_decode($decryptedRequestContent, true);
            }

            $this->tbiHelper->addLog('ORDER INFO: ',$orderInfo);

            if (!isset($orderInfo[self::PARAM_ORDER_ID])) {
                $this->tbiHelper->addLog('Missing order ID');
                throw new \Exception('Missing order ID');
            }

            $order = $this->order->loadByIncrementId($orderInfo[self::PARAM_ORDER_ID]);
            if (!$order) {
                throw new \Exception('Order with %s id does not exist',$orderInfo[self::PARAM_ORDER_ID]);
            }

            $payment = $order->getPayment();

            $this->tbiHelper->addLog('ORDER Increment Id:'.$order->getIncrementId());
            if ($payment->getMethod() != 'paymentmethod') {
                $this->tbiHelper->addLog('Wrong payment method:'.$payment->getMethod());
                return false;
            }

            if ($orderInfo[self::PARAM_STATUS_ID] == self::STATUS_APPROVED[self::PARAM_STATUS_ID]
                && in_array($orderInfo[self::PARAM_MESSAGE], self::STATUS_APPROVED[self::PARAM_MESSAGE])
            ) {
                $payment->setIsTransactionApproved(true);
                $order->setState(Order::STATE_PENDING_PAYMENT);
                $order->setStatus($this->orderConfig->getStateDefaultStatus($order->getState()));
                $this->tbiHelper->addLog('ORDER: '.$order->getIncrementId() .' - APPROVED');

            } elseif ($orderInfo[self::PARAM_STATUS_ID] == self::STATUS_CANCEL[self::PARAM_STATUS_ID]
                && in_array($orderInfo[self::PARAM_MESSAGE], self::STATUS_CANCEL[self::PARAM_MESSAGE])
            ) {
                $payment->setIsTransactionApproved(false);
                $order->setState(Order::STATE_CANCELED);
                $order->setStatus($this->orderConfig->getStateDefaultStatus($order->getState()));
                $this->tbiHelper->addLog('ORDER: '.$order->getIncrementId() .' - CANCELED');

            } elseif ($orderInfo[self::PARAM_STATUS_ID] == self::STATUS_PENDING[self::PARAM_STATUS_ID]
                && in_array($orderInfo[self::PARAM_MESSAGE], self::STATUS_PENDING[self::PARAM_MESSAGE])
            ) {
                $order->setState(Order::STATE_PROCESSING);
                $this->tbiHelper->addLog('ORDER: '.$order->getIncrementId() .' - PENDING');

            } elseif ($orderInfo[self::PARAM_STATUS_ID] == self::STATUS_REJECTED[self::PARAM_STATUS_ID]
                && in_array($orderInfo[self::PARAM_MESSAGE], self::STATUS_REJECTED[self::PARAM_MESSAGE])
            ) {
                $payment->setIsTransactionApproved(false);
                $order->setState(Order::STATE_CANCELED);
                $order->setStatus($this->orderConfig->getStateDefaultStatus($order->getState()));
                $this->tbiHelper->addLog('ORDER: '.$order->getIncrementId() .' - REJECTED');

            }

            $transactionId = $order->getIncrementId();
            $payment->setLastTransId($transactionId);
            $payment->setTransactionId($transactionId);
            $payment->setAdditionalInformation(
                [Transaction::RAW_DETAILS => (array)$orderInfo]
            );
            $formattedPrice = $order->getBaseCurrency()->formatTxt(
                $order->getGrandTotal()
            );

            $message = __('The authorized amount is %1.', $formattedPrice);
            $trans = $this->transactionBuilder;
            $transaction = $trans->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transactionId)
                ->setAdditionalInformation(
                    [Transaction::RAW_DETAILS => $orderInfo]
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

            $this->tbiHelper->addLog('ORDER: '.$order->getIncrementId() .' - DONE');

        } catch (\Exception $exception) {
            $this->tbiHelper->addLog('Exception: ' . (string)$exception->getMessage(), $exception->getTrace());
        }

    }

    /**
     * @param $json
     * @return bool
     */
    private function isJson($json)
    {
        json_decode($json);

        return (json_last_error() == JSON_ERROR_NONE);
    }
}