<?php
namespace Avalon\Costompayment\Helper;

use Avalon\Costompayment\Logger\Logger;
use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Data constructor.
     * @param Logger $logger
     * @param Context $context
     */
    public function __construct(
        Logger $logger,
        Context $context
    ) {
        parent::__construct($context);
        $this->logger = $logger;
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
                $config_path,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
	}
	
	public function getTbiroLiveUrl()
    {
        return 'https://tbicp.com';
    }

    public function addLog($message, $context = [])
    {
        if (!$this->getConfig('avalon_custompaymentmethod_tab_options/properties_tbiro/debug')) {
            return false;
        }

        try {
            $this->logger->addDebug(date('Y-m-d H:i:s').' | '. $message, $context);
        } catch (\Exception $exception) {
            $this->logger->addDebug((string)$exception->getMessage());
        }
    }
	
}
