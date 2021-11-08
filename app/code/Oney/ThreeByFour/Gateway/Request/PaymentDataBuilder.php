<?php


namespace Oney\ThreeByFour\Gateway\Request;


use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Oney\ThreeByFour\Logger\Logger;

class PaymentDataBuilder implements BuilderInterface
{

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }
        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();

        $payment = ['payment' => [
            "payment_amount" => $order->getGrandTotalAmount(),
            "currency_code" => $order->getCurrencyCode(),
            "business_transaction" => [
                "code" => str_replace('facilypay_', '', $payment->getPayment()->getMethodInstance()->getCode())
            ]
        ]];

        $this->logger->info('Builder Payment :: ',$payment);

        return $payment;
    }
}
