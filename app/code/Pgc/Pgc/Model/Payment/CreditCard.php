<?php

namespace Pgc\Pgc\Model\Payment;

use Pgc\Pgc\Model\Ui\ConfigProvider;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Exception\CouldNotSaveException;

class CreditCard extends AbstractMethod
{
    protected $_code = ConfigProvider::CREDITCARD_CODE;
    protected $_infoBlockType = \Magento\Payment\Block\Info\Cc::class;

    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canVoid = true;
    protected $_canRefund = false;

    protected $pgcHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Pgc\Pgc\Helper\Data $pgcHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->pgcHelper = $pgcHelper;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );
    }

    public function capture(InfoInterface $payment, $amount)
    {
        if ($amount > 0)
        {
            $paymentMethod = 'pgc_creditcard';

            \Pgc\Client\Client::setApiUrl($this->pgcHelper->getGeneralConfigData('host'));
            $client = new \Pgc\Client\Client(
                $this->pgcHelper->getGeneralConfigData('username'),
                $this->pgcHelper->getGeneralConfigData('password'),
                $this->pgcHelper->getPaymentConfigData('api_key', $paymentMethod, null),
                $this->pgcHelper->getPaymentConfigData('shared_secret', $paymentMethod, null)
            );
    
            $capture = new \Pgc\Client\Transaction\Capture();
            $capture->setTransactionId($payment->getOrder()->getIncrementId() . '-capture');
            $capture->setReferenceTransactionId($payment->getLastTransId());

            $capture->setAmount(\number_format($amount, 2, '.', ''));
            $capture->setCurrency($payment->getOrder()->getOrderCurrency()->getCode());;
            try
            {
                $captureResult = $client->capture($capture);
                if (!$captureResult->isSuccess()) { 
                    $msg = __('Something went wrong performing the capture.');
                    throw new \Magento\Framework\Exception\LocalizedException($msg);
                }
            }
            catch (\Exception $e)
            {
                throw new CouldNotSaveException(__('Could not capture the payment'));
            }
        }

        $payment->setParentTransactionId($payment->getLastTransId());
        $payment->setTransactionId($captureResult->getReferenceId());
        $payment->setLastTransId($captureResult->getReferenceId());

        return $this;
    }

    public function void(InfoInterface $payment, $amount = null)
    {
        $paymentMethod = 'pgc_creditcard';

        \Pgc\Client\Client::setApiUrl($this->pgcHelper->getGeneralConfigData('host'));
        $client = new \Pgc\Client\Client(
            $this->pgcHelper->getGeneralConfigData('username'),
            $this->pgcHelper->getGeneralConfigData('password'),
            $this->pgcHelper->getPaymentConfigData('api_key', $paymentMethod, null),
            $this->pgcHelper->getPaymentConfigData('shared_secret', $paymentMethod, null)
        );

        $void = new \Pgc\Client\Transaction\VoidTransaction();
        $void->setTransactionId($payment->getOrder()->getIncrementId() . '-void');
        $void->setReferenceTransactionId($payment->getLastTransId());

        try
        {
            $voidResult = $client->void($void);
            if (!$voidResult->isSuccess()) { 
                $msg = __('Something went wrong performing the void.');
                throw new \Magento\Framework\Exception\LocalizedException($msg);
            }
        }
        catch (\Exception $e)
        {
            throw new \Exception(__('Could not void payment'));
        }

        $payment->setParentTransactionId($payment->getLastTransId());
        $payment->setTransactionId($voidResult->getReferenceId());
        $payment->setLastTransId($voidResult->getReferenceId());

        return $this;
    }

    public function cancel(InfoInterface $payment, $amount = null)
    {
        $this->void($payment);

        return $this;
    }

}
