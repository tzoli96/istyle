<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Oander\FanCourierValidator\Api\Data\StateInterface;

/**
 * Class State
 * @package Oander\FanCourierValidator\Model\ResourceModel
 */
class State extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(StateInterface::TABLE_NAME, StateInterface::ENTITY_ID);
    }
}
