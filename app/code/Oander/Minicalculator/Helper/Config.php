<?php

namespace Oander\Minicalculator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{

    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     * @param $storeid
     * @return bool
     */
    public function getModuleIsActive($storeid = null)
    {
        return $this->scopeConfig->getValue("minicalculator_calculator/general/enabled", ScopeInterface::SCOPE_STORE, $storeid);
    }


}