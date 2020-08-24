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

namespace Customweb\FirstDataConnectCw\Controller\Adminhtml\Checkout;

class Success extends \Customweb\FirstDataConnectCw\Controller\Adminhtml\Checkout
{
	/**
	 * @var \Customweb\FirstDataConnectCw\Model\Authorization\Notification
	 */
	protected $_notification;

	/**
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Magento\Catalog\Helper\Product $productHelper
	 * @param \Magento\Framework\Escaper $escaper
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
	 * @param \Customweb\FirstDataConnectCw\Model\Authorization\TransactionFactory $transactionFactory
	 * @param \Customweb\FirstDataConnectCw\Model\Authorization\Notification $notification
	 */
	public function __construct(
			\Magento\Backend\App\Action\Context $context,
			\Magento\Catalog\Helper\Product $productHelper,
			\Magento\Framework\Escaper $escaper,
			\Magento\Framework\View\Result\PageFactory $resultPageFactory,
			\Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
			\Customweb\FirstDataConnectCw\Model\Authorization\TransactionFactory $transactionFactory,
			\Customweb\FirstDataConnectCw\Model\Authorization\Notification $notification
	) {
		parent::__construct($context, $productHelper, $escaper, $resultPageFactory, $resultForwardFactory, $transactionFactory);
		$this->_notification = $notification;
	}

	public function execute()
	{
		$sameSiteFix = $this->getRequest()->getParam('s');
		if (empty($sameSiteFix)) {
			header_remove('Set-Cookie');
			return $this->resultRedirectFactory->create()->setPath('firstdataconnectcw/checkout/success', [
				'cstrxid' => $this->getRequest()->getParam('cstrxid'),
				'secret' => $this->getRequest()->getParam('secret'),
				's' => 1
			]);
		} else {
			$transactionId = $this->getRequest()->getParam('cstrxid');
			$hashSecret = $this->getRequest()->getParam('secret');
			try {
				$this->_notification->waitForNotification($transactionId, 'There has been no notification from the payment provider.');
				$this->messageManager->addSuccessMessage(__('You created the order.'));
				return $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $this->getTransaction($transactionId, $hashSecret)->getOrderId()]);
			} catch (\Exception $e) {
				$this->messageManager->addErrorMessage($e->getMessage());
				return $this->resultRedirectFactory->create()->setPath('sales/order_create/reorder', ['order_id' => $this->getTransaction($transactionId, $hashSecret)->getOrderId()]);
			}
		}
	}
}