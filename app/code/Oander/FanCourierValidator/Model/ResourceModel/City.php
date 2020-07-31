<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Oander\FanCourierValidator\Api\Data\CityInterface;

/**
 * Class City
 * @package Oander\FanCourierValidator\Model\ResourceModel
 */
class City extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(CityInterface::TABLE_NAME, CityInterface::ENTITY_ID);
    }
}
