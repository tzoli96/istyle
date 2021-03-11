<?php
namespace Oander\HelloBankPayment\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Oander\HelloBankPayment\Model\HelloBank;
use Oander\HelloBankPayment\Helper\Config;
use Oander\HelloBankPayment\Gateway\Config as ConfigGateWay;

class AuthorizationHandler implements HandlerInterface
{
    /**
     * @var Config
     */
    private $configHelper;
    /**
     * @var HelloBank
     */
    private $helloBankService;

    public function __construct(
        Config $configHelper,
        HelloBank $helloBankService
    ) {
        $this->configHelper = $configHelper;
        $this->helloBankService = $helloBankService;
    }

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $payment->setIsTransactionPending(true);
        /** @var Order $order */
        $order = $payment->getOrder();

        if($response['path'] === "hellobank/payment/okstate")
        {
            $type = ConfigGateWay::HELLOBANK_REPONSE_TYPE_OK;
        }else{
            $order->setCanSendNewEmailFlag(false);
            $type = ConfigGateWay::HELLOBANK_REPONSE_TYPE_KO;
        }
        $paymentData = $this->configHelper->getPaymentData($response, $type);
        $this->helloBankService->handleStatus($order, $paymentData);

    }
}