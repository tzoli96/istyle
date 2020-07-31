<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Api\Data;

/**
 * Interface StateCityInterface
 * @package Oander\FanCourierValidator\Api\Data
 */
interface StateCityInterface
{
    const TABLE_NAME = 'oander_fan_courier_state_city';

    const ENTITY_ID = 'entity_id';
    const STATE_ID  = 'state_id';
    const CITY_ID   = 'city_id';

    /**
     * @return int
     */
    public function getStateId(): int;

    /**
     * @param int $stateId
     * @return mixed
     */
    public function setStateId(int $stateId);

    /**
     * @return int
     */
    public function getCityId(): int;

    /**
     * @param int $cityId
     * @return mixed
     */
    public function setCityId(int $cityId);
}
