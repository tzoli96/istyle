<?php
namespace Oander\HelloBankPayment\Gateway\Request;


use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Oander\HelloBankPayment\Gateway\Http\Client\HelloBankClient;

class MockDataRequest implements BuilderInterface
{
    const HELLO_BANK_URL = 'HELLO_BANK_URL';

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];
        $payment = $paymentDO->getPayment();

        $response = $payment->getAdditionalInformation('response');
        return [
            self::HELLO_BANK_URL => $response
        ];
    }
}