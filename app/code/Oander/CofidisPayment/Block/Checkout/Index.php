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

namespace Oander\CofidisPayment\Block\Checkout;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Oander\CofidisPayment\Helper\Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Oander\CofidisPayment\Helper\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->config = $config;
    }

    public function getIframeParams()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        $order = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Sales\Model\OrderFactory')->create()->load($order->getId());
        $productnames = [];
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getAllVisibleItems() as $item)
        {
            $productnames[] = $item->getName();
        }

        $postdata = array(
            'shopId'    => $this->config->getShopId(),                                          //alap
            'barem'     => $this->getRequest()->getParam("barem"),                               //alap
            'amount'    => $order->getGrandTotal(),                                             //alap
            'downpmnt'  => $this->getRequest()->getParam("downpmnt"),        //alap
            'product'   => implode("|", $productnames),                                    //alap
            'order_id'  => $order->getIncrementId(),                                            //alap
            //'deliveryMethod' => '1',                                                          //alap de nem kell
            'identification_type' => '1',                                                       //alap de fix 1
            'shop_nev' => $order->getBillingAddress()->getLastname() . ' ' . $order->getBillingAddress()->getFirstname(),    //telefonos, name of payer
            'shop_telefon' => $order->getBillingAddress()->getTelephone(),                      //telefonos, phone number of payer
            'shop_email' => $order->getBillingAddress()->getEmail(),                        //telefonos, email of payer
        );

        return $postdata;
    }

    public function getEnvironment()
    {
        return $this->config->isLive();
    }
}
