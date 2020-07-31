<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Model\ResourceModel\State;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Oander\FanCourierValidator\Api\Data\StateInterface;
use Oander\FanCourierValidator\Model\State;

/**
 * Class Collection
 * @package Oander\FanCourierValidator\Model\ResourceModel\State
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = StateInterface::ENTITY_ID;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            State::class,
            \Oander\FanCourierValidator\Model\ResourceModel\State::class
        );
    }
}