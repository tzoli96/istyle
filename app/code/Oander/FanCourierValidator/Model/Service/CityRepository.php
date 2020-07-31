<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Model\Service;

use Magento\Framework\Exception\NoSuchEntityException;
use Oander\FanCourierValidator\Api\CityRepositoryInterface;
use Oander\FanCourierValidator\Api\Data\CityInterface;
use Oander\FanCourierValidator\Api\Data\CityInterfaceFactory;
use Oander\FanCourierValidator\Model\ResourceModel\City;

/**
 * Class CityRepository
 * @package Oander\FanCourierValidator\Model\Service
 */
class CityRepository implements CityRepositoryInterface
{
    /**
     * @var CityInterfaceFactory
     */
    private $cityInterfaceFactory;

    /**
     * @var City
     */
    private $cityResource;

    /**
     * CityRepository constructor.
     * @param CityInterfaceFactory $cityInterfaceFactory
     * @param City $cityResource
     */
    public function __construct(
        CityInterfaceFactory $cityInterfaceFactory,
        City $cityResource
    ) {
        $this->cityInterfaceFactory = $cityInterfaceFactory;
        $this->cityResource = $cityResource;
    }

    /**
     * @inheritDoc
     */
    public function create(): CityInterface
    {
        return $this->cityInterfaceFactory->create();
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $id): CityInterface
    {
        /** @var CityInterface $city */
        $city = $this->cityInterfaceFactory->create();

        $this->cityResource->load($city, $id);

        if (!$city->getId()) {
            throw new NoSuchEntityException(__('City not found'));
        }

        return $city;
    }

    /**
     * @param CityInterface $city
     */
    public function save(CityInterface $city)
    {
        $this->cityResource->save($city);

        return $city;
    }

    /**
     * @param array $cities
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insertMultipleCities(array $cities)
    {
        $tableName = $this->cityResource->getMainTable();
        $connection = $this->cityResource->getConnection();

        return $connection->insertMultiple($tableName, $cities);
    }

    public function truncate()
    {
        $connection = $this->cityResource->getConnection();
        $tableName = $this->cityResource->getMainTable();
        $connection->delete($tableName);
    }
}
