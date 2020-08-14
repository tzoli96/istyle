<?php
/**
 * Loan Payment modul for Cofidis
 * Copyright (C) 2019 
 * 
 * This file included in Oander/CofidisPayment is licensed under OSL 3.0
 * 
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Oander\CofidisPayment\Controller\Checkout;

class Index extends \Magento\Checkout\Controller\Onepage
{

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $session = $this->getOnepage()->getCheckout();
        /*if (!$this->_objectManager->get('Magento\Checkout\Model\Session\SuccessValidator')->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }*/
        $resultPage = $this->resultPageFactory->create();
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_cofidis_action',
            ['order_ids' => [$session->getLastRealOrder()->getIncrementId()]]
        );
        return $resultPage;
    }
}
