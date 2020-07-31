<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Api;

use Oander\FanCourierValidator\Api\Data\StateInterface;

/**
 * Interface StateRepositoryInterface
 * @package Oander\FanCourierValidator\Api
 */
interface StateRepositoryInterface
{
    /**
     * @return StateInterface
     */
    public function create(): StateInterface;

    /**
     * @param int $id
     *
     * @return StateInterface
     */
    public function get(int $id): StateInterface;

    /**
     * @param StateInterface $state
     *
     * @return void
     */
    public function save(StateInterface $state);
}
