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
use Oander\FanCourierValidator\Api\Data\CityInterface;

/**
 * Class City
 * @package Oander\FanCourierValidator\Model
 */
class City extends AbstractModel implements CityInterface, IdentityInterface
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(\Oander\FanCourierValidator\Model\ResourceModel\City::class);
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
    public function getCity(): string
    {
        return (string)$this->getData(self::CITY);
    }

    /**
     * @inheritDoc
     */
    public function setCity(string $city)
    {
        $this->setData(self::CITY, $city);
    }
}
