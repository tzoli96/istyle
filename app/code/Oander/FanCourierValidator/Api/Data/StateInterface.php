<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Api\Data;

/**
 * Interface StateInterface
 * @package Oander\FanCourierValidator\Api\Data
 */
interface StateInterface
{
    const TABLE_NAME = 'oander_fan_courier_state';

    const ENTITY_ID = 'entity_id';
    const STATE     = 'state';

    /**
     * @return string
     */
    public function getState(): string;

    /**
     * @param string $state
     * @return mixed
     */
    public function setState(string $state);
}
