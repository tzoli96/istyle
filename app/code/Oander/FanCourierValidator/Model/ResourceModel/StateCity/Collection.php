<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Model\ResourceModel\StateCity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Oander\FanCourierValidator\Api\Data\StateCityInterface;
use Oander\FanCourierValidator\Model\StateCity;

/**
 * Class Collection
 * @package Oander\FanCourierValidator\Model\ResourceModel\StateCity
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = StateCityInterface::ENTITY_ID;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            StateCity::class,
            \Oander\FanCourierValidator\Model\ResourceModel\StateCity::class
        );
    }
}