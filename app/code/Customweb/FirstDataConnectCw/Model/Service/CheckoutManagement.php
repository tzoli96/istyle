<?php
/**
  * You are allowed to use this API in your web application.
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
 * @category	Customweb
 * @package		Customweb_FirstDataConnectCw
 *
 */

namespace Customweb\FirstDataConnectCw\Model\Service;

class CheckoutManagement implements \Customweb\FirstDataConnectCw\Api\CheckoutManagementInterface
{
	/**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $_quoteIdMaskFactory;

	/**
	 *
	 * @var \Magento\Payment\Helper\Data
	 */
	protected $_paymentHelper;

	/**
	 *
	 * @var \Customweb\FirstDataConnectCw\Model\Payment\Method\ConfigProvider
	 */
	protected $_configProvider;

	/**
	 * @var \Customweb\FirstDataConnectCw\Model\Authorization\Method\Factory
	 */
	protected $_authorizationMethodFactory;

	/**
	 *
	 * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
	 * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
	 * @param \Magento\Payment\Helper\Data $paymentHelper
	 * @param \Customweb\FirstDataConnectCw\Model\Payment\Method\ConfigProvider $configProvider
	 * @param \Customweb\FirstDataConnectCw\Model\Authorization\Method\Factory $authorizationMethodFactory
	 */
	public function __construct(
		\Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
	    \Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
		\Magento\Payment\Helper\Data $paymentHelper,
		\Customweb\FirstDataConnectCw\Model\Payment\Method\ConfigProvider $configProvider,
		\Customweb\FirstDataConnectCw\Model\Authorization\Method\Factory $authorizationMethodFactory
	){
		$this->_quoteRepository = $quoteRepository;
		$this->_orderRepository = $orderRepository;
		$this->_checkoutSession = $checkoutSession;
		$this->_quoteIdMaskFactory = $quoteIdMaskFactory;
		$this->_paymentHelper = $paymentHelper;
		$this->_configProvider = $configProvider;
		$this->_authorizationMethodFactory = $authorizationMethodFactory;
	}

	public function authorize($orderId, array $formValues = null)
	{
	    if ($this->_checkoutSession->getLastOrderId() != $orderId) {
	        throw new \Magento\Framework\Exception\LocalizedException(
                \__('You are not allowed to process the order.')
            );
	    }
		$context = $this->_authorizationMethodFactory->getContextFactory()->createTransaction(null, $this->_orderRepository->get($orderId), $this->convertFormValuesToMap($formValues));
		$authorizationMethodAdapter = $this->_authorizationMethodFactory->create($context);
		return $authorizationMethodAdapter->startAuthorization();
	}

	public function guestAuthorize($cartId, $orderId, array $formValues = null)
	{
		return $this->authorize($orderId, $formValues);
	}

    public function getPaymentForm($cartId, $paymentMethod, $alias = null)
    {
    		return new PaymentForm($this->buildPaymentForm($cartId, $paymentMethod, $alias));
    }

    public function getGuestPaymentForm($cartId, $paymentMethod, $alias = null)
    {
	    	$quoteIdMask = $this->_quoteIdMaskFactory->create()->load($cartId, 'masked_id');
	    	return new PaymentForm($this->buildPaymentForm($quoteIdMask->getQuoteId(), $paymentMethod, $alias));
    }

    /**
     *
     * @param int $quoteId
     * @param string $paymentMethod
     * @param int $alias
     * @return string
     * @throws \Customweb\FirstDataConnectCw\Model\Exception\OptimisticLockingException
     */
    protected function buildPaymentForm($quoteId, $paymentMethod, $alias = null)
    {
	    	$paymentMethodInstance = $this->_paymentHelper->getMethodInstance($paymentMethod);
	    	$quote = $this->_quoteRepository->get($quoteId);
	    	$alias = $alias != 0 ? $alias : null;

	    	$lastException = null;
	    	for ($i = 0; $i < 10; $i++) {
	    		try {
	    			return $this->_configProvider->getForm($paymentMethodInstance, $quote, $alias);
	    		}
	    		catch (\Customweb\FirstDataConnectCw\Model\Exception\OptimisticLockingException $e) {
	    			// Try again.
	    			$lastException = $e;
	    		}
	    	}
	    	throw $lastException;
    }

    /**
     *
     * @param \Customweb\FirstDataConnectCw\Api\Data\AuthorizationFormFieldInterface[] $formValues
     * @return NULL|string[]
     */
    protected function convertFormValuesToMap(array $formValues = null) {
    		if ($formValues == null) {
    			return null;
    		}

    		$map = [];
    		foreach ($formValues as $formValue) {
    			/* @var \Customweb\FirstDataConnectCw\Api\Data\AuthorizationFormFieldInterface $formValue */
    			$map[$formValue->getKey()] = $formValue->getValue();
    		}
    		return $map;
    }

}