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

namespace Customweb\FirstDataConnectCw\Model\BackendOperation;

class CancelAdapter implements \Customweb_Payment_BackendOperation_Adapter_Shop_ICancel
{
	/**
	 * @var \Magento\Sales\Api\OrderRepositoryInterface
	 */
	protected $_orderRepository;

	/**
	 * @var \Customweb\FirstDataConnectCw\Model\Authorization\TransactionFactory
	 */
	protected $_transactionFactory;

	/**
	 * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
	 * @param \Customweb\FirstDataConnectCw\Model\Authorization\TransactionFactory $transactionFactory
	 */
	public function __construct(
			\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
			\Customweb\FirstDataConnectCw\Model\Authorization\TransactionFactory $transactionFactory
	) {
		$this->_orderRepository = $orderRepository;
		$this->_transactionFactory = $transactionFactory;
	}

	public function cancel(\Customweb_Payment_Authorization_ITransaction $transaction)
	{
		$this->registerCancel($transaction);
	}

	/**
	 * @param \Customweb_Payment_Authorization_ITransaction $transaction
	 */
	private function registerCancel(\Customweb_Payment_Authorization_ITransaction $transaction)
	{
		/* @var $transactionEntity \Customweb\FirstDataConnectCw\Model\Authorization\Transaction */
		$transactionEntity = $this->_transactionFactory->create()->load($transaction->getTransactionId());
		if (!$transactionEntity->getId()) {
			throw new \Exception('The transaction has not been found.');
		}
		$transactionEntity->getOrderPayment()->registerVoidNotification();
		$order = $transactionEntity->getOrderPayment()->getOrder();
		$order->addRelatedObject($transactionEntity->getOrderPayment());
		$this->_orderRepository->save($order);
	}
}