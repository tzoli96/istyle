<?php

namespace Oney\ThreeByFour\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Oney\ThreeByFour\Logger\Logger;

class CancelDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    protected $_subjectReader;
    /**
     * @var Logger
     */
    protected $_logger;

    public function __construct(
        SubjectReader $subjectReader,
        Logger $logger
    )
    {
        $this->_logger = $logger;
        $this->_subjectReader = $subjectReader;
    }

    public function build(array $buildSubject)
    {
        $paymentDO = $this->_subjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $this->_logger->info('Oney :: Cancel build payment :', $order->getData());
        $payment = $paymentDO->getPayment();
        $this->_logger->info('Oney :: Cancel build payment :', $payment->getData());

        $response = [
            "purchase" => [

            ]
        ];

        $this->_logger->info('Oney :: Cancel build :', $response);
        return $response;
    }
}
