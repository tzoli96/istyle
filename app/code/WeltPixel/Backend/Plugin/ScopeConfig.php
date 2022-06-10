<?php

namespace WeltPixel\Backend\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ScopeConfig
{
    /**
     * @param ScopeConfigInterface $subject
     * @param \Closure $proceed
     * @param $path
     * @param $scopeType
     * @param $scopeCode
     * @return bool|mixed
     */
    public function aroundIsSetFlag(
        ScopeConfigInterface $subject,
        \Closure             $proceed,
                             $path,
                             $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                             $scopeCode = null
    )
    {
        $originalResult = $proceed($path, $scopeType, $scopeCode);
        if ($path == 'csp/mode/storefront/report_only') {
            if ($subject->getValue('weltpixel_backend_developer/csp/change_system_value')) {
                return (boolean)$subject->getValue('weltpixel_backend_developer/csp/report_only');
            }
        }
        return $originalResult;
    }
}
