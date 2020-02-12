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

use Bigfishpaymentgateway\Pmgw\Gateway\Helper\Helper;
use Bigfishpaymentgateway\Pmgw\Model\Log;
use Bigfishpaymentgateway\Pmgw\Model\Transaction;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;

/**
 * Class Success
 * @package Oander\IstyleCustomization\Bloc
 */
class Success extends \Bigfishpaymentgateway\Pmgw\Block\Success
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderConfig
     */
    private $orderConfig;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * Success constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param OrderConfig $orderConfig
     * @param HttpContext $httpContext
     * @param Helper $helper
     * @param JsonHelper $jsonHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderConfig $orderConfig,
        HttpContext $httpContext,
        Helper $helper, JsonHelper
        $jsonHelper,
        array $data = []
    ) {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $helper, $jsonHelper, $data);

        $this->checkoutSession = $checkoutSession;
        $this->orderConfig = $orderConfig;
        $this->httpContext = $httpContext;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;

        $this->_isScopePrivate = true;
    }

    protected function prepareBlockData()
    {
        /** @var Order $order */
        $order = $this->checkoutSession->getLastRealOrder();

        if (!$order || !$order->getId()) {
            return;
        }

        $transactionId = $this->getTransactionId($order);

        if (!$transactionId) {
            return;
        }

        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();

        $this->addData([
            'order_id'  => $order->getIncrementId(),
            'order_updated_at'  => $order->getUpdatedAt(),
            'order_currency_code'  => $order->getBaseCurrencyCode(),
            'order_total'  => (float)$order->getGrandTotal(),
            'method_title'  => $method->getTitle(),
            'can_view_order' => $this->canViewOrder($order),
            'response' => $this->getTransactionData($transactionId),
        ]);
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function canViewOrder(Order $order)
    {
        return false;
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function isVisible(Order $order)
    {
        return !in_array(
            $order->getStatus(),
            $this->orderConfig->getInvisibleOnFrontStatuses()
        );
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
