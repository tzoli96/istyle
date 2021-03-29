<?php
namespace Oander\HelloBankPayment\Gateway\Response;

use Magento\Sales\Model\Order;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class InitHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        $stateObject = $this->subjectReader->readStateObject($handlingSubject);
        $stateObject->setState(Order::STATE_PENDING_PAYMENT);
    }
}