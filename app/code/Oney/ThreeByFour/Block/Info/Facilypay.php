<?php

namespace Oney\ThreeByFour\Block\Info;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Block\Info;
use Oney\ThreeByFour\Helper\Config as HelperConfig;

class Facilypay extends Info
{
    /**
     * @var HelperConfig
     */
    protected $helperConfig;

    public function __construct(
        Template\Context $context,
        HelperConfig $helperConfig,
        array $data = [])
    {
        $this->helperConfig = $helperConfig;
        parent::__construct($context, $data);
        $this->checkAndSetTemplate('payment/info.phtml');
    }

    protected function checkAndSetTemplate($template)
    {
        if($this->helperConfig->getCountrySpecificationsConfigValue('country')) {
            if ($this->getTemplateFile(
                $this->helperConfig->getCountrySpecificationsConfigValue('country')
                .'/'.$template)
            ) {
                $template = $this->helperConfig->getCountrySpecificationsConfigValue('country') .'/'.$template;
            }
            $this->setTemplate($template);
        }
    }
}
