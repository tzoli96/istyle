<?php

namespace Oney\ThreeByFour\Model\Method;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Adapter;
use Magento\Payment\Model\MethodInterface;
use Oney\ThreeByFour\Api\FacilypayMethodInterface;
use Oney\ThreeByFour\Helper\Config;
use Psr\Log\LoggerInterface;
use Oney\ThreeByFour\Logger\Logger as OneyLogger;

class Facilypay extends DataObject implements MethodInterface, FacilypayMethodInterface
{
    const METHOD_CODE = "oney_facilypay";

    const STATUS_FUNDED = "FUNDED";
    const STATUS_PENDING = "PENDING";
    const STATUS_CANCELLED = "CANCELLED";
    const STATUS_REFUSED = "REFUSED";
    const STATUS_ABORTED = "ABORTED";
    /**
     * @var Config
     */
    protected $_helperConfig;
    /**
     * @var string
     */
    protected $code = self::METHOD_CODE;
    /**
     * @var Adapter
     */
    protected $_adapter;
    /**
     * @var OneyLogger
     */
    protected $_logger;

    public function __construct(
        Adapter $adapter,
        Config $config,
        OneyLogger $logger,
        array $data = []
    )
    {
        $this->_adapter = $adapter;
        $this->_helperConfig = $config;
        $this->_logger = $logger;
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @inheritDoc
     */
    public function getFormBlockType()
    {
        return $this->_adapter->getFormBlockType();
    }

    /**
     * @inheritDoc
     */
    public function setStore($storeId)
    {
        $this->setData("store", $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getStore()
    {
        return $this->getData("store");
    }

    /**
     * @inheritDoc
     */
    public function canOrder()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canAuthorize()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function canCapture()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canCapturePartial()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function canCaptureOnce()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canRefund()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canRefundPartialPerInvoice()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canVoid()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canUseInternal()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canUseCheckout()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canEdit()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function canFetchTransactionInfo()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        return $this->_adapter->fetchTransactionInfo($payment, $transactionId);
    }

    /**
     * @inheritDoc
     */
    public function isGateway()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isOffline()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isInitializeNeeded()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function canUseForCountry($country)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canUseForCurrency($currencyCode)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getInfoBlockType()
    {
        return $this->_adapter->getInfoBlockType();
    }

    /**
     * @inheritDoc
     */
    public function getInfoInstance()
    {
        return $this->_adapter->getInfoBlockType();
    }

    /**
     * @inheritDoc
     */
    public function setInfoInstance(InfoInterface $info)
    {
        $this->_adapter->setInfoInstance($info);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $this->_adapter->validate();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function order(InfoInterface $payment, $amount)
    {
        $this->_logger->info('Oney :: order start');
        $this->_adapter->order($payment, $amount);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        $this->_adapter->authorize($payment, $amount);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function capture(InfoInterface $payment, $amount)
    {
        //$this->_adapter->capture($payment, $amount);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $this->_adapter->refund($payment, $amount);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function cancel(InfoInterface $payment)
    {
        $this->_adapter->cancel($payment);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function void(InfoInterface $payment)
    {
        $this->_adapter->void($payment);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function canReviewPayment()
    {
        return $this->_adapter->canReviewPayment();
    }

    /**
     * @inheritDoc
     */
    public function acceptPayment(InfoInterface $payment)
    {
        $this->_adapter->acceptPayment($payment);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function denyPayment(InfoInterface $payment)
    {
        $this->_adapter->denyPayment($payment);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->getData($field);
    }

    /**
     * @inheritDoc
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        $this->setData($data->toArray());
    }

    /**
     * @inheritDoc
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $this->_logger->debug("Oney :: isAvailable :",$this->toArray());
        return ($quote->getGrandTotal() >= $this->getMinOrderTotal() &&
            $quote->getGrandTotal() <= $this->getMaxOrderTotal());
    }

    /**
     * @inheritDoc
     */
    public function isActive($storeId = null)
    {
        return true;
        return $this->_helperConfig->isPaymentActiveForCode($this->getCode(), $storeId);
    }

    /**
     * @inheritDoc
     */
    public function initialize($paymentAction, $stateObject)
    {
        return $this->_adapter->initialize($paymentAction, $stateObject);
    }

    /**
     * @inheritDoc
     */
    public function getConfigPaymentAction()
    {
        return AbstractMethod::ACTION_ORDER;
    }

    /**
     * @param string $code
     *
     * @return MethodInterface
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    public function getMaxOrderTotal()
    {
        return $this->getData('max_order_total_oney');
    }

    public function getMinOrderTotal()
    {
        return $this->getData('min_order_total_oney');
    }

    public function getTitle()
    {
        return $this->getData('title');
    }
}
