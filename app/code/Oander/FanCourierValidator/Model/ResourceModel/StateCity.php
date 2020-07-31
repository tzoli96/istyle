<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Oander\FanCourierValidator\Api\Data\StateCityInterface;

/**
 * Class StateCity
 * @package Oander\FanCourierValidator\Model\ResourceModel
 */
class StateCity extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(StateCityInterface::TABLE_NAME, StateCityInterface::ENTITY_ID);
    }
}
