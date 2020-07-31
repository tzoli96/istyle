<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Model\Service;

use Magento\Framework\Exception\NoSuchEntityException;
use Oander\FanCourierValidator\Api\Data\CityInterface;
use Oander\FanCourierValidator\Api\Data\StateInterface;
use Oander\FanCourierValidator\Api\StateCityRepositoryInterface;
use Oander\FanCourierValidator\Api\Data\StateCityInterface;
use Oander\FanCourierValidator\Api\Data\StateCityInterfaceFactory;
use Oander\FanCourierValidator\Model\ResourceModel\StateCity;

/**
 * Class StateCityRepository
 * @package Oander\FanCourierValidator\Model\Service
 */
class StateCityRepository implements StateCityRepositoryInterface
{
    /**
     * @var StateCityRepositoryInterface
     */
    private $stateCityInterfaceFactory;

    /**
     * @var StateCity
     */
    private $stateCityResource;

    /**
     * StateCityRepository constructor.
     * @param StateCityInterfaceFactory $stateCityInterfaceFactory
     * @param StateCity $stateCityResource
     */
    public function __construct(
        StateCityInterfaceFactory $stateCityInterfaceFactory,
        StateCity $stateCityResource
    ) {
        $this->stateCityInterfaceFactory = $stateCityInterfaceFactory;
        $this->stateCityResource = $stateCityResource;
    }

    /**
     * @inheritDoc
     */
    public function create(): StateCityInterface
    {
        return $this->stateCityInterfaceFactory->create();
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $id): StateCityInterface
    {
        /** @var StateCityInterface $stateCity */
        $stateCity = $this->stateCityInterfaceFactory->create();

        $this->stateCityResource->load($stateCity, $id);

        if (!$stateCity->getId()) {
            throw new NoSuchEntityException(__('StateCity not found'));
        }

        return $stateCity;
    }

    /**
     * @param StateCityInterface $stateCity
     */
    public function save(StateCityInterface $stateCity)
    {
        $this->stateCityResource->save($stateCity);

        return $stateCity;
    }

    public function getCollection()
    {
        $stateCityCollection = $this->create()->getCollection();
        $stateCityCollection->join(
            ['city' => $this->stateCityResource->getTable(CityInterface::TABLE_NAME)],
            "main_table.city_id = city.entity_id",
            ['city']
        );
        $stateCityCollection->join(
            ['state' => $this->stateCityResource->getTable(StateInterface::TABLE_NAME)],
            "main_table.state_id = state.entity_id",
            ['state']
        );

        return $stateCityCollection;
    }

    /**
     * @param array $stateCityPairs
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insertMultipleStateCityPairs(array $stateCityPairs)
    {
        $tableName = $this->stateCityResource->getMainTable();
        $connection = $this->stateCityResource->getConnection();

        return $connection->insertMultiple($tableName, $stateCityPairs);
    }

    public function truncate()
    {
        $connection = $this->stateCityResource->getConnection();
        $tableName = $this->stateCityResource->getMainTable();
        $connection->delete($tableName);
    }
}
