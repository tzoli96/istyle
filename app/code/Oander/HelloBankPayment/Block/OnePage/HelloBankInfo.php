<?php
namespace Oander\HelloBankPayment\Block\OnePage;

use Magento\Framework\View\Element\Template;
use Oander\HelloBankPayment\Helper\Config;

class HelloBankInfo extends Template
{
    /**
     * @var Config
     */
    private $helper;

    /**
     * HelloBankInfo constructor.
     * @param Template\Context $context
     * @param Config $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $helper,
        array $data = []
    ){
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isHelloBank()
    {
        return $this->getRequest()->getParam("is_hellobank");
    }

    /**
     * @return string
     */
    public function getSuccessPageMessage()
    {
        return $this->helper->getSucessPageMessage();
    }
}