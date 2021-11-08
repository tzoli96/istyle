<?php

namespace Oney\ThreeByFour\Model\Payment;

use Magento\Payment\Model\Method\Factory;
use Oney\ThreeByFour\Api\Marketing\BusinessTransactionsInterface;
use Oney\ThreeByFour\Api\PaymentMethodListInterface;
use Oney\ThreeByFour\Model\Method\Facilypay;
use Psr\Log\LoggerInterface;

class PaymentMethodList implements PaymentMethodListInterface
{
    /**
     * @var Factory
     */
    protected $_methodsFromOney;
    /**
     * @var array
     */
    protected $paymentMethods;
    /**
     * @var Factory
     */
    protected $_methodFactory;
    /**
     * @var BusinessTransactionsInterface
     */
    protected $_businessTransactions;
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    public function __construct(
        Factory $methodFactory,
        BusinessTransactionsInterface $businessTransactions,
        LoggerInterface $logger
    )
    {
        $this->_methodFactory = $methodFactory;
        $this->_logger = $logger;
        $this->_businessTransactions = $businessTransactions;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethod($method)
    {
        if (empty($this->_methodsFromOney)) {
            $this->_methodsFromOney = $this->_businessTransactions->getBusinessTransactions();
        }
        if (!isset($this->paymentMethods[$method])) {
            if (isset($this->_methodsFromOney[$method])) {
                $this->_logger->debug("Oney :: getPaymentMethod :", $this->_methodsFromOney[$method]);
                $oneyMethod = $this->_methodsFromOney[$method];
            }
            else {
                $oneyMethod = [
                    "min_order_total" => 0,
                    "max_order_total" => 0
                ];
            }
            $this->paymentMethods[$method] = $this->_methodFactory->create(Facilypay::class)
                ->setCode($method)
                ->setMaxOrderTotalOney($oneyMethod['max_order_total'])
                ->setMinOrderTotalOney($oneyMethod['min_order_total']);
        }
        return $this->paymentMethods[$method];
    }
}
