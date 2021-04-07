<?php
namespace Oander\HelloBankPayment\Block\OnePage;

use Magento\Framework\View\Element\Template;

class HelloBankInfo extends Template
{
    /**
     * @return bool
     */
    public function isHelloBank()
    {
        return $this->getRequest()->getParam("is_hellobank");
    }
}