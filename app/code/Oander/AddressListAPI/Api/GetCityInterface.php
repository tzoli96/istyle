<?php

namespace Oander\AddressListAPI\Api;

interface GetCityInterface
{
    /**
     * @return string[]
     **/
    public function getAllRegion();

    /**
     * @return string[]
     **/
    public function getAllCity();

    /**
     * @param string $region
     * @return string[]
     **/
    public function getByRegion($region);
}