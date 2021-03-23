<?php
namespace Oander\HelloBankPayment\Controller\Payment;

use Magento\Framework\App\ResponseInterface;
use Oander\HelloBankPayment\Gateway\Config;
use Oander\HelloBankPayment\Gateway\Config as ConfigGateWay;
use Oander\HelloBankPayment\Gateway\Config\ConfigValueHandler;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Oander\HelloBankPayment\Gateway\Config as ConfigHelper;
use Oander\HelloBankPayment\Helper\Config as PaymentHelper;
use Oander\HelloBankPayment\Model\HelloBank;

class Redirect extends Action
{
    /**
     * @var HelloBank
     */
    private $helloBankService;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ConfigValueHandler
     */
    private $config;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $param = [];

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * Redirect constructor.
     *
     * @param HelloBank $helloBankService
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param ConfigValueHandler $config
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        HelloBank $helloBankService,
        Context $context,
        CheckoutSession $checkoutSession,
        ConfigValueHandler $config,
        PaymentHelper $paymentHelper
    ) {
        $this->helloBankService = $helloBankService;
        $this->paymentHelper = $paymentHelper;
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
    }

    /**
     * @return RedirectResult
     */
    public function execute()
    {
        /** @var RedirectResult $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('checkout/onepage/success');

        /** @var Order $order */
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order instanceof Order) {
            /** @var Payment $payment */
            $payment = $order->getPayment();
            $response = $payment->getAdditionalInformation('response');
            switch ($response)
            {
                case Config::HELLOBANK_REPONSE_TYPE_OK:
                    $response = [
                        'path'          => 'hellobank/payment/okstate',
                        'stav'          => 1,
                        'numaut'        => $this->generateTxnId(),
                        'numwrk'        => 1,
                        'jmeno'         => 1,
                        'prijmeni'      => 1,
                        'splatka'       => 1,
                        'numklient'     => 1,
                        'obj'           => $order->getIncrementId(),
                    ];
                    break;

                case Config::HELLOBANK_REPONSE_TYPE_KO:
                    $response = [
                        'path'          => 'hellobank/payment/kostate',
                        'stav'          => 2,
                        'vdr'           => 2,
                        'numwrk'        => 1,
                        'jmeno'         => 1,
                        'prijmeni'      => 1,
                        'splatka'       => 1,
                        'numklient'     => 1,
                        'obj'           => $order->getIncrementId(),
                    ];
                default:
            }

            if($response['path'] === "hellobank/payment/okstate")
            {
                $type = ConfigGateWay::HELLOBANK_REPONSE_TYPE_OK;
            }else{
                $type = ConfigGateWay::HELLOBANK_REPONSE_TYPE_KO;
            }
            $paymentData = $this->paymentHelper->getPaymentData($response, $type);
            $this->helloBankService->handleStatus($order, $paymentData);
        }

        return $resultRedirect;
    }

    /**
     * @return string
     */
    protected function generateTxnId()
    {
        return md5(mt_rand(0, 1000));
    }
}
