<?php

namespace Oander\OneyThreeByFourExtender\Plugin;

use Oney\ThreeByFour\Gateway\Http\TransferFactory;

class TransferFactoryReplaceZipCode
{
    /**
     * @param TransferFactory $subject
     * @param $request
     * @return array[]
     */
    public function beforeCreate(TransferFactory $subject, $request)
    {
        $request['customer']['customer_address']['postal_code'] = 999999;
        $request['purchase']['delivery']['delivery_address']['postal_code'] = 999999;
        return [$request];
    }
}