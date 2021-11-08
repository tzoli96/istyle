<?php

namespace Oney\ThreeByFour\Plugin\Payment\Helper;

use Magento\Payment\Model\MethodInterface;
use Oney\ThreeByFour\Model\Api\Marketing\BusinessTransactions;
use Oney\ThreeByFour\Api\PaymentMethodListInterface;
use Psr\Log\LoggerInterface;

class DataPlugin
{
    /**
     * @var BusinessTransactions
     */
    protected $_businessTransactions;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var PaymentMethodListInterface
     */
    protected $paymentMethodList;

    /**
     * DataPlugin constructor.
     *
     * @param BusinessTransactions       $businessTransactions
     * @param LoggerInterface            $logger
     * @param PaymentMethodListInterface $paymentMethodList
     */
    public function __construct(
        BusinessTransactions $businessTransactions,
        LoggerInterface $logger,
        PaymentMethodListInterface $paymentMethodList
    )
    {
        $this->_businessTransactions = $businessTransactions;
        $this->logger = $logger;
        $this->paymentMethodList = $paymentMethodList;
    }

    public function afterGetPaymentMethods(\Magento\Payment\Helper\Data $subject, $result)
    {
        return array_merge($this->_businessTransactions->getBusinessTransactions(), $result);
    }


    /**
     * Modify results of getMethodInstance() call to add in details about Klarna payment methods
     *
     * @param \Magento\Payment\Helper\Data $subject
     * @param callable                     $proceed
     * @param string                       $code
     *
     * @return MethodInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function aroundGetMethodInstance(\Magento\Payment\Helper\Data $subject, callable $proceed, $code)
    {
        $this->logger->info('Oney :: aroundGetMethodInstance :', [$code]);
        if (false === strpos($code, 'facilypay_')) {
            return $proceed($code);
        }
        return $this->paymentMethodList->getPaymentMethod($code);
    }
}
