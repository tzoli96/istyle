<?php
/**
 *  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2018 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 *
 * @category Customweb
 * @package Customweb_FirstDataConnectCw
 *
 */
namespace Customweb\FirstDataConnectCw\Model\Payment\Method;

class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod implements \Customweb_Payment_Authorization_IPaymentMethod
{
	/**
	 * @var \Magento\Quote\Api\CartRepositoryInterface
	 */
	protected $_quoteRepository;

	/**
	 *
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $_checkoutSession;

	/**
	 *
	 * @var \Magento\Framework\App\RequestInterface
	 */
	protected $_request;

	/**
	 *
	 * @var \Magento\Framework\DB\TransactionFactory
	 */
	protected $_dbTransactionFactory;

	/**
	 * @var \Magento\Framework\Encryption\EncryptorInterface
	 */
	protected $_encryptor;

	/**
	 * @var \Magento\Framework\Pricing\PriceCurrencyInterface
	 */
	protected $_priceCurrency;

	/**
	 *
	 * @var \Customweb\FirstDataConnectCw\Model\Authorization\Method\Factory
	 */
	protected $_authorizationMethodFactory;

	/**
	 *
	 * @var \Customweb\FirstDataConnectCw\Model\Configuration
	 */
	protected $_configuration;

	/**
	 *
	 * @var \Customweb\FirstDataConnectCw\Model\DependencyContainer
	 */
	protected $_container;

	/**
	 *
	 * @var \Customweb\FirstDataConnectCw\Model\Authorization\TransactionFactory
	 */
	protected $_transactionFactory;

	/**
	 *
	 * @var \Customweb\FirstDataConnectCw\Helper\InvoiceItem
	 */
	protected $_invoiceItemHelper;

	/**
	 * @var \Customweb\FirstDataConnectCw\Helper\FoomanSurcharge
	 */
	protected $_foomanSurchargeHelper;

	/**
	 * Payment method code
	 *
	 * @var string
	 */
	protected $_code;

	/**
	 * Payment method name
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * Form block paths
	 *
	 * @var string
	 */
	protected $_formBlockType = 'Customweb\FirstDataConnectCw\Block\Payment\Method\Form';

	/**
	 * Info block path
	 *
	 * @var string
	 */
	protected $_infoBlockType = 'Customweb\FirstDataConnectCw\Block\Payment\Method\Info';

	/**
	 *
	 * @param \Magento\Framework\Model\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
	 * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
	 * @param \Magento\Payment\Helper\Data $paymentData
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param \Magento\Payment\Model\Method\Logger $logger
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
	 * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Framework\App\RequestInterface $request
	 * @param \Magento\Framework\DB\TransactionFactory $dbTransactionFactory
	 * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
	 * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
	 * @param \Customweb\FirstDataConnectCw\Model\Authorization\Method\Factory $authorizationMethodFactory
	 * @param \Customweb\FirstDataConnectCw\Model\Configuration $configuration
	 * @param \Customweb\FirstDataConnectCw\Model\DependencyContainer $container
	 * @param \Customweb\FirstDataConnectCw\Model\Authorization\TransactionFactory $transactionFactory
	 * @param \Customweb\FirstDataConnectCw\Helper\InvoiceItem $invoiceItemHelper
	 * @param \Customweb\FirstDataConnectCw\Helper\FoomanSurcharge $foomanSurchargeHelper
	 * @param array $data
	 */
	public function __construct(
			\Magento\Framework\Model\Context $context,
			\Magento\Framework\Registry $registry,
			\Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
			\Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
			\Magento\Payment\Helper\Data $paymentData,
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Magento\Payment\Model\Method\Logger $logger,
			\Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
			\Magento\Checkout\Model\Session $checkoutSession,
			\Magento\Framework\App\RequestInterface $request,
			\Magento\Framework\DB\TransactionFactory $dbTransactionFactory,
			\Magento\Framework\Encryption\EncryptorInterface $encryptor,
			\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
			\Customweb\FirstDataConnectCw\Model\Authorization\Method\Factory $authorizationMethodFactory,
			\Customweb\FirstDataConnectCw\Model\Configuration $configuration,
			\Customweb\FirstDataConnectCw\Model\DependencyContainer $container,
			\Customweb\FirstDataConnectCw\Model\Authorization\TransactionFactory $transactionFactory,
			\Customweb\FirstDataConnectCw\Helper\InvoiceItem $invoiceItemHelper,
			\Customweb\FirstDataConnectCw\Helper\FoomanSurcharge $foomanSurchargeHelper,
			\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
			\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
			array $data = []
	) {
		parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $resource,
				$resourceCollection, $data);
		$this->_quoteRepository = $quoteRepository;
		$this->_checkoutSession = $checkoutSession;
		$this->_request = $request;
		$this->_dbTransactionFactory = $dbTransactionFactory;
		$this->_encryptor = $encryptor;
		$this->_priceCurrency = $priceCurrency;
		$this->_authorizationMethodFactory = $authorizationMethodFactory;
		$this->_configuration = $configuration;
		$this->_container = $container;
		$this->_transactionFactory = $transactionFactory;
		$this->_invoiceItemHelper = $invoiceItemHelper;
		$this->_foomanSurchargeHelper = $foomanSurchargeHelper;
	}

	public function setStore($storeId)
	{
		parent::setStore($storeId);
		$this->_configuration->setStore($storeId);
	}

	public function getPaymentMethodName()
	{
		return $this->_name;
	}

	public function getPaymentMethodDisplayName()
	{
		return $this->getPaymentMethodConfigurationValue('title');
	}

	public function getPaymentMethodConfigurationValue($key, $languageCode = null)
	{
		$rawValue = $this->_configuration->getConfigurationValue('payment', $this->_code . '/' . $key);
		if (\in_array($this->_code . '/' . $key, [
			
		])) {
			return $this->_encryptor->decrypt($rawValue);
		} else {
			return $rawValue;
		}
	}

	public function existsPaymentMethodConfigurationValue($key, $languageCode = null)
	{
		return $this->_configuration->existsConfiguration('payment', $this->_code . '/' . $key);
	}

	/**
	 * Get description text
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return trim($this->getPaymentMethodConfigurationValue('description'));
	}

	/**
	 * Should show image
	 *
	 * @return boolean
	 */
	public function isShowImage()
	{
		return (boolean) $this->getPaymentMethodConfigurationValue('show_image');
	}

	/**
	 * Should use base currency
	 *
	 * @return boolean
	 */
	public function isUseBaseCurrency()
	{
		return (boolean) $this->getPaymentMethodConfigurationValue('base_currency');
	}

	/**
	 *
	 * @return string
	 */
	public function getOrderPlaceRedirectUrl()
	{
		$quote = $this->_checkoutSession->getQuote();

		$transactionId = null;
		$transaction = $this->_registry->registry('firstdataconnectcw_transaction');
		if ($transaction instanceof \Customweb\FirstDataConnectCw\Model\Authorization\Transaction) {
			$transactionId = $transaction->getId();
		}
		return $quote->getStore()->getUrl('firstdataconnectcw/checkout/error', [
			'_secure' => true,
			'transaction_id' => $transactionId
		]);
	}

	public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
	{
		$isAvailable = parent::isAvailable($quote);

		if ($isAvailable) {
			$allowedCurrencies = $this->getPaymentMethodConfigurationValue('currency');
			if ($quote !== null && !empty($allowedCurrencies)) {
				$isAvailable = (in_array($quote->getCurrency()->getQuoteCurrencyCode(), $allowedCurrencies));
			}
		}

		if ($isAvailable) {
			try {
				$context = $this->getAuthorizationMethodFactory()->getContextFactory()->createQuote($this, $quote);
				$adapter = $this->getAuthorizationMethodFactory()->create($context);
				$adapter->preValidate();
				$isAvailable = $context->getOrderContext()->isValid();
			}
			catch (\Exception $e) {
				$isAvailable = false;
			}
		}

		return $isAvailable;
	}

	public function validate()
	{
		
		$arguments = null;
		return \Customweb_Licensing_FirstDataConnectCw_License::run('md9l2vvcsb0390lj', $this, $arguments);
	}

	final public function call_30u34ed2arftb95t() {
		$arguments = func_get_args();
		$method = $arguments[0];
		$call = $arguments[1];
		$parameters = array_slice($arguments, 2);
		if ($call == 's') {
			return call_user_func_array(array(get_class($this), $method), $parameters);
		}
		else {
			return call_user_func_array(array($this, $method), $parameters);
		}
		
		
	}
	private function parentValidate()
	{
		parent::validate();
	}

	/**
	 * Set initial order status to pending payment.
	 *
	 * @param string $paymentAction
	 * @param \Magento\Framework\DataObject $stateObject
	 * @return \Customweb\FirstDataConnectCw\Model\Payment\Method\AbstractMethod
	 */
	public function initialize($paymentAction, $stateObject)
	{
		$state = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;
		$stateObject->setState($state);
		$stateObject->setStatus('pending_payment');
		$stateObject->setIsNotified(false);
		return $this;
	}

	/**
	 * Set transaction id and set transaction as pending if authorization is uncertain.
	 *
	 * @param \Magento\Payment\Model\InfoInterface $payment
	 * @param float $amount
	 * @return \Customweb\FirstDataConnectCw\Model\Payment\Method\AbstractMethod
	 */
	public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
	{
		parent::authorize($payment, $amount);

		$transaction = $this->_registry->registry('firstdataconnectcw_authorization_transaction');
		if ($transaction instanceof \Customweb\FirstDataConnectCw\Model\Authorization\Transaction) {
			$payment->setIsTransactionClosed(false);
			if ($transaction->getTransactionObject()->isAuthorizationUncertain()) {
				$payment->setIsTransactionPending(true);
			}
		}
		return $this;
	}

	/**
	 * Capture amount online.
	 *
	 * @param \Magento\Payment\Model\InfoInterface $payment
	 * @param float $amount
	 * @return \Customweb\FirstDataConnectCw\Model\Payment\Method\AbstractMethod
	 */
	public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
	{
		parent::capture($payment, $amount);

		


		return $this;
	}

	private function convertCaptureAmount($amount, \Magento\Sales\Model\Order $order, $invoice) {
		if ($invoice instanceof \Magento\Sales\Model\Order\Invoice) {
			$amount = $this->_priceCurrency->round($amount * $invoice->getBaseToOrderRate());
			return \min($amount, $invoice->getGrandTotal());
		} else {
			$amount = $this->_priceCurrency->round($amount * $order->getBaseToOrderRate());
			return \min($amount, $order->getGrandTotal());
		}
	}

	/**
	 * Refund amount online.
	 *
	 * @param \Magento\Payment\Model\InfoInterface $payment
	 * @param float $amount
	 * @return \Customweb\FirstDataConnectCw\Model\Payment\Method\AbstractMethod
	 */
	public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
	{
		parent::refund($payment, $amount);

		


		return $this;
	}

	private function convertRefundAmount($amount, \Magento\Sales\Model\Order $order, $creditmemo) {
		if ($creditmemo instanceof \Magento\Sales\Model\Order\Creditmemo) {
			$amount = $this->_priceCurrency->round($amount * $creditmemo->getBaseToOrderRate());
			return \min($amount, $creditmemo->getGrandTotal());
		} else {
			$amount = $this->_priceCurrency->round($amount * $order->getBaseToOrderRate());
			return \min($amount, $order->getGrandTotal());
		}
	}

	/**
	 * Void amount online.
	 *
	 * @param \Magento\Payment\Model\InfoInterface $payment
	 * @return \Customweb\FirstDataConnectCw\Model\Payment\Method\AbstractMethod
	 */
	public function void(\Magento\Payment\Model\InfoInterface $payment)
	{
		parent::void($payment);

		


		return $this;
	}

	public function acceptPayment(\Magento\Payment\Model\InfoInterface $payment)
	{
		$transaction = $this->_transactionFactory->create()->loadByOrderPaymentId($payment->getId());
		if ($transaction->getId()) {
			if ($transaction->getTransactionObject()->isCapturePossible()) {
				$this->captureItems($transaction, $transaction->getTransactionObject()->getUncapturedLineItems());
			}
		} else {
			throw new \Magento\Framework\Exception\LocalizedException(__('The transaction cannot be loaded.'));
		}
		return true;
	}

	public function denyPayment(\Magento\Payment\Model\InfoInterface $payment)
	{
		$transaction = $this->_transactionFactory->create()->loadByOrderPaymentId($payment->getId());
		if ($transaction->getId()) {
			if ($transaction->getTransactionObject()->isCancelPossible()) {
				$this->void($payment);
			}
			elseif ($transaction->getTransactionObject()->isCaptured()) {
				// TODO: If transaction is captured, we need to issue a refund.
			}
		} else {
			throw new \Magento\Framework\Exception\LocalizedException(__('The transaction cannot be loaded.'));
		}
		return true;
	}

	public function assignData(\Magento\Framework\DataObject $data)
	{
		parent::assignData($data);
		$infoInstance = $this->getInfoInstance();
		//Since 2.1 the alias and form values are stored in the additional_data array
		if ($data->getData('additional_data') !== null) {
			$infoInstance->setAdditionalInformation('alias', $data->getData('additional_data/alias'));
			foreach ($data->getData('additional_data') as $key => $value) {
				if (strpos($key, 'form[') === 0) {
					$infoInstance->setAdditionalInformation(substr($key, 5, -1), $value);
				}
			}
		}
		else {
			$infoInstance->setAdditionalInformation('alias', $data->getData('alias'));

			foreach ($data->getData() as $key => $value) {
				if (strpos($key, 'form[') === 0) {
					$infoInstance->setAdditionalInformation(substr($key, 5, -1), $value);
				}
			}
		}
		return $this;
	}

	private function captureItems(\Customweb\FirstDataConnectCw\Model\Authorization\Transaction $transaction, $items = [])
	{
		
	}

	/**
	 *
	 * @return boolean
	 */
	private function isCaptureNoClose()
	{
		if ($this->_request->getParam('capture_no_close')) {
			return true;
		}
		$invoice = $this->_request->getParam('invoice');
		if (is_array($invoice) && isset($invoice['capture_no_close']) && $invoice['capture_no_close']) {
			return true;
		}
		return false;
	}

	private function getAuthorizationMethodFactory()
	{
		return $this->_authorizationMethodFactory;
	}

	private function getRegistry()
	{
		return $this->_registry;
	}
}
