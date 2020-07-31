<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Api;

use Oander\FanCourierValidator\Api\Data\StateCityInterface;

/**
 * Interface StateCityRepositoryInterface
 * @package Oander\FanCourierValidator\Api
 */
interface StateCityRepositoryInterface
{
    /**
     * @return StateCityInterface
     */
    public function create(): StateCityInterface;

    /**
     * @param int $id
     *
     * @return StateCityInterface
     */
    public function get(int $id): StateCityInterface;

    /**
     * @param StateCityInterface $stateCity
     *
     * @return void
     */
    public function save(StateCityInterface $stateCity);
}
