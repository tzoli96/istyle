<?php

namespace Oander\CofidisPayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    public function getRedirectUrl(string $urlpath)
    {
        $params["_secure"] = true;
        return $this->_getUrl($urlpath, $params);
    }
}