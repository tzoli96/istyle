<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Model\Service;

use Magento\Framework\Exception\NoSuchEntityException;
use Oander\FanCourierValidator\Api\StateRepositoryInterface;
use Oander\FanCourierValidator\Api\Data\StateInterface;
use Oander\FanCourierValidator\Api\Data\StateInterfaceFactory;
use Oander\FanCourierValidator\Model\ResourceModel\State;

/**
 * Class StateRepository
 * @package Oander\FanCourierValidator\Model\Service
 */
class StateRepository implements StateRepositoryInterface
{
    /**
     * @var StateRepositoryInterface
     */
    private $stateInterfaceFactory;

    /**
     * @var State
     */
    private $stateResource;

    /**
     * StateRepository constructor.
     * @param StateInterfaceFactory $stateInterfaceFactory
     * @param State $stateResource
     */
    public function __construct(
        StateInterfaceFactory $stateInterfaceFactory,
        State $stateResource
    ) {
        $this->stateInterfaceFactory = $stateInterfaceFactory;
        $this->stateResource = $stateResource;
    }

    /**
     * @inheritDoc
     */
    public function create(): StateInterface
    {
        return $this->stateInterfaceFactory->create();
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $id): StateInterface
    {
        /** @var StateInterface $state */
        $state = $this->stateInterfaceFactory->create();

        $this->stateResource->load($state, $id);

        if (!$state->getId()) {
            throw new NoSuchEntityException(__('State not found'));
        }

        return $state;
    }

    /**
     * @param StateInterface $state
     */
    public function save(StateInterface $state)
    {
        $this->stateResource->save($state);

        return $state;
    }

    /**
     * @param array $states
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insertMultipleStates(array $states)
    {
        $tableName = $this->stateResource->getMainTable();
        $connection = $this->stateResource->getConnection();

        return $connection->insertMultiple($tableName, $states);
    }

    public function truncate()
    {
        $connection = $this->stateResource->getConnection();
        $tableName = $this->stateResource->getMainTable();
        $connection->delete($tableName);
    }
}
