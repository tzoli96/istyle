<?php
namespace Oander\CustomerAddressValidation\Block\Checkout;

use Magento\Framework\View\Element\Template;

class Checkout extends Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get store code
     * 
     * @return string
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }
}
