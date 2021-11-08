<?php

namespace Oney\ThreeByFour\Plugin\Sales\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Oney\ThreeByFour\Api\Payment\ConfirmInterface;

class OrderPlugin
{
    /**
     * @var ConfirmInterface
     */
    private $_confirmService;

    public function __construct(
        ConfirmInterface $confirmService
    )
    {
        $this->_confirmService = $confirmService;
    }

    public function afterCanShip(OrderInterface $subject, $result) {
        if(preg_match("/^facilypay/", $subject->getPayment()->getMethod(), $test) &&
            $subject->getStatus() != 'processing'
        ) {
            return false;
        }
        return $result;
    }

    public function afterCanInvoice(OrderInterface $subject, $result) {
        if(preg_match("/^facilypay/", $subject->getPayment()->getMethod(), $test)
        ) {
            return false;
        }
        return $result;
    }
}
