<?php
namespace Avalon\Costompayment\Block;

class Tbirocreateid extends \Magento\Framework\View\Element\Template
{
	
	protected $_checkoutSession;
	protected $_orderFactory;
	protected $_scopeConfig;
	protected $_order_id;
	
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ){
		parent::__construct($context);
		$this->_checkoutSession = $checkoutSession;
		$this->_orderFactory = $orderFactory;
		$this->_scopeConfig = $context->getScopeConfig();
		$this->_order_id = $this->getRealOrderId();
	}
	
    public function getRealOrderId()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
		$orderId= $order->getEntityId();
        return $order->getIncrementId();
    }

    public function getOrder()
    {
        $order = $this->_orderFactory->create()->loadByIncrementId($this->_order_id);
        return $order;
    }

    public function getShippingInfo()
    {
        $order = $this->getOrder();
        if($order) {
            $address = $order->getShippingAddress();    
			
            return $address;
        }
        return false;
    }
	
}