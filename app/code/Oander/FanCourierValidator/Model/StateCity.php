<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\AbstractModel;
use Oander\FanCourierValidator\Api\Data\StateCityInterface;

/**
 * Class StateCity
 * @package Oander\FanCourierValidator\Model
 */
class StateCity extends AbstractModel implements StateCityInterface, IdentityInterface
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(\Oander\FanCourierValidator\Model\ResourceModel\StateCity::class);
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        return [self::TABLE_NAME . '_' . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function getStateId(): int
    {
        return (string)$this->getData(self::STATE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStateId(int $stateId)
    {
        $this->setData(self::STATE_ID, $stateId);
    }

    /**
     * @inheritDoc
     */
    public function getCityId(): int
    {
        return (string)$this->getData(self::CITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCityId(int $cityId)
    {
        $this->setData(self::CITY_ID, $cityId);
    }
}
