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
use Oander\FanCourierValidator\Api\Data\StateInterface;

/**
 * Class State
 * @package Oander\FanCourierValidator\Model
 */
class State extends AbstractModel implements StateInterface, IdentityInterface
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(\Oander\FanCourierValidator\Model\ResourceModel\State::class);
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
    public function getState(): string
    {
        return (string)$this->getData(self::STATE);
    }

    /**
     * @inheritDoc
     */
    public function setState(string $state)
    {
        $this->setData(self::STATE, $state);
    }
}
