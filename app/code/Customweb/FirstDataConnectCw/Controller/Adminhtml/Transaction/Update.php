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

namespace Customweb\FirstDataConnectCw\Controller\Adminhtml\Transaction;

class Update extends \Customweb\FirstDataConnectCw\Controller\Adminhtml\Transaction
{
	public function execute()
	{
		$transaction = $this->_initTransaction();
		

		$resultRedirect = $this->resultRedirectFactory->create();
		$orderId = $this->getRequest()->getParam('order_id');
		if ($orderId) {
			$resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
		} elseif ($transaction) {
			$resultRedirect->setPath('firstdataconnectcw/*/view', ['transaction_id' => $transaction->getId()]);
		} else {
			$resultRedirect->setPath('firstdataconnectcw/*');
		}
		return $resultRedirect;
	}
}