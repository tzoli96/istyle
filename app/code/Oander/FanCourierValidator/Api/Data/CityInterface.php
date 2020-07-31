<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Api\Data;

/**
 * Interface CityInterface
 * @package Oander\FanCourierValidator\Api\Data
 */
interface CityInterface
{
    const TABLE_NAME = 'oander_fan_courier_city';

    const ENTITY_ID = 'entity_id';
    const CITY     = 'city';

    /**
     * @return string
     */
    public function getCity(): string;

    /**
     * @param string $state
     * @return mixed
     */
    public function setCity(string $state);
}
