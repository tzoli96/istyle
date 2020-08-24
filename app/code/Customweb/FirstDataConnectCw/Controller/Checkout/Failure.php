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

namespace Customweb\FirstDataConnectCw\Controller\Checkout;

class Failure extends \Customweb\FirstDataConnectCw\Controller\Checkout
{
	public function execute()
	{
		$sameSiteFix = $this->getRequest()->getParam('s');
		if (empty($sameSiteFix)) {
			header_remove('Set-Cookie');
			return $this->resultRedirectFactory->create()->setPath('firstdataconnectcw/checkout/failure', [
				'cstrxid' => $this->getRequest()->getParam('cstrxid'),
				'secret' => $this->getRequest()->getParam('secret'),
				's' => 1,
				'_secure' => true
			]);
		} else {
			try {
				$transaction = $this->getTransaction($this->getRequest()->getParam('cstrxid'), $this->getRequest()->getParam('secret'));
				return $this->handleFailure($transaction, $transaction->getLastErrorMessage());
			} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
				return $this->resultRedirectFactory->create()->setPath('checkout/cart');
			}
		}
	}
}