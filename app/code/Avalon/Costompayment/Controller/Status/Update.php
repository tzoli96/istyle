<?php

namespace Avalon\Costompayment\Controller\Status;

use Magento\Sales\Model\Order;

/**
 * Class Update
 * @package Avalon\Costompayment\Controller\Status
 */
class Update extends \Magento\Framework\App\Action\Action
{
    const ACTION_KEY = 'costompayment/status/update';

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
     * Tbigetid constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\Data\OrderInterface $order,
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
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Avalon\Costompayment\Helper\Data $tbiHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->order = $order;
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
        try {
            $requestContent = $this->getRequest()->getContent();
            file_put_contents(
                '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                date('Y-m-d H:i:s') . ' | UPDATE: ' . var_export($requestContent, true) . PHP_EOL,
                FILE_APPEND
            );

            if ($this->isJson($requestContent)) {
                file_put_contents(
                    '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                    date('Y-m-d H:i:s') . ' | isJson! ' . PHP_EOL,
                    FILE_APPEND
                );
                $orderInfo = json_decode($requestContent, true);
            } else {
                file_put_contents(
                    '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                    date('Y-m-d H:i:s') . ' | NOT Json! ' . PHP_EOL,
                    FILE_APPEND
                );
                if (!$privateKey = openssl_pkey_get_private(file_get_contents($this->filesystem
                        ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
                        ->getAbsolutePath() . 'keys/private_bg.key'))
                ) {
                    file_put_contents(
                        '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                        date('Y-m-d H:i:s') . ' | Private Key failed ' . PHP_EOL,
                        FILE_APPEND
                    );
                    throw new \Exception('Private Key failed');
                }

                file_put_contents(
                    '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                    date('Y-m-d H:i:s') . ' | Private Key OK! ' . PHP_EOL,
                    FILE_APPEND
                );
                $a_key = openssl_pkey_get_details($privateKey);
                $chunkSize = ceil($a_key['bits'] / 8);
                $decryptedRequestContent = '';
                $encrypted = base64_decode($requestContent);
                while ($encrypted) {
                    $chunk = substr($encrypted, 0, $chunkSize);
                    $encrypted = substr($encrypted, $chunkSize);
                    $decrypted = '';
                    if (!openssl_private_decrypt($chunk, $decrypted, $privateKey)) {
                        file_put_contents(
                            '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                            date('Y-m-d H:i:s') . ' | Failed to decrypt data ' . PHP_EOL,
                            FILE_APPEND
                        );
                        throw new \Exception('Failed to decrypt data');
                    }
                    $decryptedRequestContent .= $decrypted;
                }
                openssl_free_key($privateKey);

                $orderInfo = json_decode($decryptedRequestContent, true);
            }

            file_put_contents(
                '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                date('Y-m-d H:i:s') . ' | ORDERINFO: ' . var_export($orderInfo, true) . PHP_EOL,
                FILE_APPEND
            );


            $order = $this->order->loadByIncrementId($orderInfo[self::PARAM_ORDER_ID]);
            //$order = $this->orderRepository->get($orderInfo[self::PARAM_ORDER_ID]);
            $payment = $order->getPayment();

            file_put_contents(
                '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                date('Y-m-d H:i:s') . ' | ORDER'.$order->getIncrementId() . PHP_EOL,
                FILE_APPEND
            );

            if ($payment->getMethod() != 'paymentmethod') {
                file_put_contents(
                    '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                    date('Y-m-d H:i:s') . ' | wrong payment method'.$payment->getMethod() . PHP_EOL,
                    FILE_APPEND
                );
                return false;
            }

            if ($orderInfo[self::PARAM_STATUS_ID] == self::STATUS_APPROVED[self::PARAM_STATUS_ID]
                && in_array($orderInfo[self::PARAM_MESSAGE], self::STATUS_APPROVED[self::PARAM_MESSAGE])
            ) {
                $payment->setIsTransactionApproved(true);

                file_put_contents(
                    '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                    date('Y-m-d H:i:s') . ' | ORDER: '.$order->getIncrementId() .' -APPROVED'. PHP_EOL,
                    FILE_APPEND
                );
            } elseif ($orderInfo[self::PARAM_STATUS_ID] == self::STATUS_CANCEL[self::PARAM_STATUS_ID]
                && in_array($orderInfo[self::PARAM_MESSAGE], self::STATUS_CANCEL[self::PARAM_MESSAGE])
            ) {
                $payment->setIsTransactionApproved(false);
                $order->setState(Order::STATE_CANCELED);

                file_put_contents(
                    '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                    date('Y-m-d H:i:s') . ' | ORDER: '.$order->getIncrementId() .' -CANCELED'. PHP_EOL,
                    FILE_APPEND
                );
            } elseif ($orderInfo[self::PARAM_STATUS_ID] == self::STATUS_PENDING[self::PARAM_STATUS_ID]
                && in_array($orderInfo[self::PARAM_MESSAGE], self::STATUS_PENDING[self::PARAM_MESSAGE])
            ) {
                $order->setState(Order::STATE_PENDING_PAYMENT);

                file_put_contents(
                    '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                    date('Y-m-d H:i:s') . ' | ORDER: '.$order->getIncrementId() .' -PENDING'. PHP_EOL,
                    FILE_APPEND
                );
            } elseif ($orderInfo[self::PARAM_STATUS_ID] == self::STATUS_REJECTED[self::PARAM_STATUS_ID]
                && in_array($orderInfo[self::PARAM_MESSAGE], self::STATUS_REJECTED[self::PARAM_MESSAGE])
            ) {
                $payment->setIsTransactionApproved(false);
                $order->setState(Order::STATE_CANCELED);

                file_put_contents(
                    '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                    date('Y-m-d H:i:s') . ' | ORDER: '.$order->getIncrementId() .' -REJECTED'. PHP_EOL,
                    FILE_APPEND
                );
            }

            $payment->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => $orderInfo]
            );

            $this->orderRepository->save($order);

            file_put_contents(
                '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                date('Y-m-d H:i:s') . ' | ORDER: '.$order->getIncrementId() .' -DONE!'. PHP_EOL,
                FILE_APPEND
            );

        } catch (\Exception $exception) {
            file_put_contents(
                '/var/www/istyle.eu/webroot/var/log/oander/tbi.log',
                date('Y-m-d H:i:s') . ' | Exception: ' . (string)$exception->getMessage() . PHP_EOL,
                FILE_APPEND
            );
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