<?php
/**
 * BIG FISH Ltd.
 * http://www.bigfish.hu
 *
 * @title      BIG FISH Payment Gateway module for Magento 2
 * @category   BigFish
 * @package    Bigfishpaymentgateway_Pmgw
 * @author     BIG FISH Ltd., paymentgateway [at] bigfish [dot] hu
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2017, BIG FISH Ltd.
 */
namespace Oander\IstyleCustomization\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Bigfishpaymentgateway\Pmgw\Gateway\Helper\Helper;
use Bigfishpaymentgateway\Pmgw\Model\Log;
use Bigfishpaymentgateway\Pmgw\Model\Transaction;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Class Info
 * @package Oander\IstyleCustomization\Block
 */
class Info extends \Bigfishpaymentgateway\Pmgw\Block\Info
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * Info constructor.
     *
     * @param Context         $context
     * @param ConfigInterface $config
     * @param Helper          $helper
     * @param JsonHelper      $jsonHelper
     * @param array           $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        Session $checkoutSession,
        Helper $helper,
        JsonHelper $jsonHelper,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);

        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * {@inehritdoc}
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);

        $order = $this->checkoutSession->getLastRealOrder();
        if (!$order || !$order->getId()) {
            return $transport;
        }

        $transactionId = $this->getTransactionId($order);
        if (!$transactionId) {
            return $transport;
        }

        $transactionData = $this->getTransactionData($transactionId);
        if (property_exists($transactionData, 'ResultMessage')) {
            $this->_paymentSpecificInformation->setData((string) __('Result message: '), $transactionData->ResultMessage);
        }

        if (property_exists($transactionData, 'ProviderTransactionId')) {
            $this->_paymentSpecificInformation->setData((string) __('Provider transaction id: '), $transactionData->ProviderTransactionId);
        }

        if (property_exists($transactionData, 'Anum')) {
            $this->_paymentSpecificInformation->setData((string) __('Anum: '), $transactionData->Anum);
        }

        return $this->_paymentSpecificInformation;
    }

    /**
     * @param Order $order
     * @return null|string
     */
    private function getTransactionId(Order $order)
    {
        /** @var OrderPaymentInterface $payment */
        $payment = $order->getPayment();

        if (!$payment) {
            return null;
        }
        return $payment->getLastTransId();
    }

    /**
     * @param $transactionId
     * @return object|null
     */
    private function getTransactionData($transactionId)
    {
        /** @var Transaction $transaction */
        $transaction = $this->helper->getTransactionByTransactionId($transactionId);

        if (!$transaction || !$transaction->getId()) {
            return null;
        }

        /** @var Log $transactionLog */
        $transactionLog = $this->helper->getTransactionLog($transaction);

        if (!$transactionLog || !$transactionLog->getId()) {
            return null;
        }

        try {
            return (object)$this->jsonHelper->jsonDecode($transactionLog->getData('debug'));
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return null;
    }
}
