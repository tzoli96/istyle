<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Api;

use Oander\FanCourierValidator\Api\Data\CityInterface;

/**
 * Interface CityRepositoryInterface
 * @package Oander\FanCourierValidator\Api
 */
interface CityRepositoryInterface
{
    /**
     * @return CityInterface
     */
    public function create(): CityInterface;

    /**
     * @param int $id
     *
     * @return CityInterface
     */
    public function get(int $id): CityInterface;

    /**
     * @param CityInterface $city
     *
     * @return void
     */
    public function save(CityInterface $city);
}
