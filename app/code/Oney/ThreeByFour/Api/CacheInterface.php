<?php


namespace Oney\ThreeByFour\Api;


interface CacheInterface
{
    /**
     * @param array $data
     *
     * @return bool
     */
    public function save($data);
    /**
     * @return string
     */
    public function load();

    /**
     * @param string $type
     *
     * @return self
     */
    public function setCache($type);

    /**
     * @param string $country
     *
     * @return self
     */
    public function setOneyCountry($country);
}
