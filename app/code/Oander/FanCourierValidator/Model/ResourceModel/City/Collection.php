<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Model\ResourceModel\City;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Oander\FanCourierValidator\Api\Data\CityInterface;
use Oander\FanCourierValidator\Model\City;

/**
 * Class Collection
 * @package Oander\FanCourierValidator\Model\ResourceModel\City
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = CityInterface::ENTITY_ID;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            City::class,
            \Oander\FanCourierValidator\Model\ResourceModel\City::class
        );
    }
}