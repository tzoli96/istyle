<?php


namespace Oander\DebugForDev\Observer\Frontend\Layout;

class GenerateBlocksAfter implements \Magento\Framework\Event\ObserverInterface
{

    /** @var \Psr\Log\LoggerInterface  */
    protected $_logger;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Layout constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct (
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->scopeConfig->getValue('oanderdebug/layout/enabled', 'store')) {
            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $xml = $observer->getEvent()->getLayout()->getXmlString();
            $logdir = BP . '/var/log/debug/layout';
            if (!file_exists($logdir)) {
                mkdir($logdir, 0777, true);
            }
            $writer = new \Zend\Log\Writer\Stream($logdir . '/' . $this->clean($actual_link) . '.xml');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($xml);
            return $this;
        }
        else
        {
            return $this;
        }
    }

    function clean($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
}
