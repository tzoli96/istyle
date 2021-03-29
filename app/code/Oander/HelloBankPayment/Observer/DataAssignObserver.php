<?php
namespace Oander\HelloBankPayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class DataAssignObserver extends AbstractDataAssignObserver
{
    const PAYMENT_RESPONSE_RESULT = 'response';
    const PAYMENT_RESPONSE_VALUES = 'values';

    private $additionalInformationList = [
            'kodBaremu',
            'kodPojisteni',
            'cenaZbozi',
            'primaPlatba',
            'vyseUveru',
            'pocetSplatek',
            'odklad',
            'vyseSplatky',
            'cenaUveru',
            'RPSN',
            'ursaz',
            'celkovaCastka',
    ];

    public function execute(Observer $observer)
    {

        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (array_key_exists($additionalInformationKey, $additionalData)) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}