<?php

namespace Avalon\Costompayment\Controller\Status;

use Magento\Sales\Model\Order;

/**
 * Class Update
 * @package Avalon\Costompayment\Controller\Status
 */
class Update extends \Magento\Framework\App\Action\Action
{
    const ACTION_KEY = '/costompayment/status/update';

    const PARAM_ORDER_ID = 'order_id';
    const PARAM_STATUS_ID = 'status_id';
    const PARAM_MESSAGE = 'motiv';

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
     * Tbigetid constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Avalon\Costompayment\Helper\Data $tbiHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Avalon\Costompayment\Helper\Data $tbiHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->tbiHelper = $tbiHelper;
        $this->imageHelper = $imageHelper;
        $this->filesystem = $filesystem;
        return parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        file_put_contents('/var/www/istyle.eu/webroot/var/log/oander/tbi.log', date('Y-m-d H:i:s').' | UPDATE: '. var_export($params,true).PHP_EOL,FILE_APPEND);

        $orderData = $this->getRequest()->getParam('order_data',false);

        if (!$orderData) {
            return false;
        }

        // Get the private Key
       /* if (!$privateKey = openssl_pkey_get_private(file_get_contents('keys/pkey'),
            'password')) {
            die('Private Key failed');
        }

        var_dump($a_key);

       */
        $privateKey = openssl_pkey_get_public(
            file_get_contents(
                $this->filesystem
                    ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
                    ->getAbsolutePath() . 'keys/public.key'
            )
        );

        $a_key = openssl_pkey_get_details($privateKey);
        $chunkSize = ceil($a_key['bits'] / 8);
        $decryptedOrderData = '';
        $encrypted = base64_decode($orderData);
        while ($encrypted) {
            $chunk = substr($encrypted, 0, $chunkSize);
            $encrypted = substr($encrypted, $chunkSize);
            $decrypted = '';
            if (!openssl_private_decrypt($chunk, $decrypted, $privateKey)) {
                die('Failed to decrypt data');
            }
            $decryptedOrderData .= $decrypted;
        }
        openssl_free_key($privateKey);


        $orderInfo = json_decode($decryptedOrderData);
        $order = $this->orderRepository->get($orderInfo[self::PARAM_ORDER_ID]);
        $payment = $order->getPayment();

        if ($orderInfo[self::PARAM_STATUS_ID] == self::STATUS_APPROVED[self::PARAM_STATUS_ID]
            && in_array( self::STATUS_APPROVED[self::PARAM_MESSAGE],$orderInfo[self::PARAM_MESSAGE])
        ) {
            $payment->setIsTransactionApproved(true);
        } elseif ($orderInfo[self::PARAM_STATUS_ID] == self::STATUS_CANCEL[self::PARAM_STATUS_ID]
            && in_array( self::STATUS_CANCEL[self::PARAM_MESSAGE],$orderInfo[self::PARAM_MESSAGE])
        ) {
            $payment->setIsTransactionApproved(false);
            $order->setState(Order::STATE_CANCELED);
        } elseif ($orderInfo[self::PARAM_STATUS_ID] == self::STATUS_PENDING[self::PARAM_STATUS_ID]
            && in_array( self::STATUS_PENDING[self::PARAM_MESSAGE],$orderInfo[self::PARAM_MESSAGE])
        ) {
            $order->setState(Order::STATE_PENDING_PAYMENT);
        } elseif ($orderInfo[self::PARAM_STATUS_ID] == self::STATUS_REJECTED[self::PARAM_STATUS_ID]
            && in_array( self::STATUS_REJECTED[self::PARAM_MESSAGE],$orderInfo[self::PARAM_MESSAGE])
        ) {
            $payment->setIsTransactionApproved(false);
            $order->setState(Order::STATE_CANCELED);
        }

        $payment->setAdditionalInformation(
            [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => $orderInfo]
        );

        $this->orderRepository->save($order);

    }
}