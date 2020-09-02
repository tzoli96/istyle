<?php

namespace Oander\CofidisPayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Oander\CofidisPayment\Enum\Ownshare;

class Data extends AbstractHelper
{

    public function getRedirectUrl(string $urlpath)
    {
        $params["_secure"] = true;
        return $this->_getUrl($urlpath, $params);
    }

    /**
     * @param $total
     * @param $ownshare
     * @return bool
     */
    public function isAllowedByMinimumTotalAmount($total, $ownshare)
    {
        if(isset($ownshare[Ownshare::MINIMUM_LOAN])) {
            if (is_string($ownshare[Ownshare::MINIMUM_LOAN])) {
                //Nem éri el a minimum hitel összegét
                if(((float)$total) < ((float)$ownshare[Ownshare::MINIMUM_LOAN]))
                {
                    return false;
                }

                //Nézzük meg a minimum önrész aktív lesz-e már az elején
                if(((float)$ownshare[Ownshare::MINIMUM_LOAN]) >= ((float)$ownshare[Ownshare::OWNSHARE_PRICE_LIMIT]))
                {
                    //Ha igen akkor ugye az összeg nem lehet kisebb mint minimum loan és a minimum önrész összege
                    if(((float)$total) < ((float)$ownshare[Ownshare::MINIMUM_LOAN] + (float)$ownshare[Ownshare::OWNSHARE_PRICE_LIMIT]))
                    {
                        return false;
                    }

                    //Ha igen akkor ugye az összeg nem lehet kisebb mint minimum loan és százalékos önrész értéke
                    if(((float)$total) < ((float)$ownshare[Ownshare::MINIMUM_LOAN] + (((float)$ownshare[Ownshare::OWNSHARE_PERCENTAGE]/100) * (float)$total)))
                    {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}