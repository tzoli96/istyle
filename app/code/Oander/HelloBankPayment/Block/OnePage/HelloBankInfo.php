<?php
namespace Oander\HelloBankPayment\Block\OnePage;

use Magento\Framework\View\Element\Template;

class HelloBankInfo extends Template
{
    public function getParams($var)
    {
        return $this->getRequest()->getParam($var);
    }

    public function ddParams()
    {
        return $this->getRequest()->getParams();
    }

}