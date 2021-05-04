<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\LiveSale\Plugin\Frontend\Magento\Framework\App\Action;

use Magento\Framework\App\ResponseInterface;

class Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Action constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function afterDispatch(
        \Magento\Framework\App\Action\Action $subject,
        $result
    ) {
        if($this->scopeConfig->isSetFlag("livesale/general/enabled",\Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            if($this->scopeConfig->getValue("livesale/general/policy_url",\Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $result->setHeader("Content-Security-Policy", "frame-ancestors " . $this->scopeConfig->getValue("livesale/general/policy_url", \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            }
        }
        return $result;
    }
}