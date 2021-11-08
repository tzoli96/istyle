<?php

namespace Oney\ThreeByFour\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class CancelHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    protected $_subjectReader;

    public function __construct(
        SubjectReader $subjectReader
    )
    {
        $this->_subjectReader = $subjectReader;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->_subjectReader::readPayment($handlingSubject);
        /** @var Payment $orderPayment */
        $orderPayment = $paymentDO->getPayment();
        $orderPayment->setIsTransactionClosed(true);
        $orderPayment->setShouldCloseParentTransaction(true);
    }
}
