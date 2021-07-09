<?php

namespace Oander\MPTrade\Api;

interface MPTradeInterface
{
    /**
     * @param string $param
     * @param string $param2
     * @param string $param3
     * @return mixed
     */
    public function getData($param, $param2 = null, $param3 = null);
}