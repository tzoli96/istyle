<?php
namespace Avalon\Costompayment\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	public function getConfig($config_path){
    return $this->scopeConfig->getValue(
			$config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	
	public function getTbiroLiveUrl()
    {
        return 'https://tbicp.com';
    }
	
}
