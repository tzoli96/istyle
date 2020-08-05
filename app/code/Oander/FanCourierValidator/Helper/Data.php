<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Helper;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Oander\FanCourierValidator\Api\StateRepositoryInterface;
use Oander\FanCourierValidator\Api\StateCityRepositoryInterface;
use Oander\FanCourierValidator\Api\CityRepositoryInterface;
use Oander\FanCourierValidator\Model\Cache\Validator;

/**
 * Class Data
 * @package Oander\FanCourierValidator\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var StateRepositoryInterface
     */
    private $stateRepository;

    /**
     * @var CityRepositoryInterface
     */
    private $cityRepository;

    /**
     * @var StateCityRepositoryInterface
     */
    private $stateCityRepository;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * Data constructor.
     * @param StateRepositoryInterface $stateRepository
     * @param CityRepositoryInterface $cityRepository
     * @param StateCityRepositoryInterface $stateCityRepository
     * @param CacheInterface $cache
     * @param Context $context
     */
    public function __construct(
        StateRepositoryInterface $stateRepository,
        CityRepositoryInterface $cityRepository,
        StateCityRepositoryInterface $stateCityRepository,
        CacheInterface $cache,
        Context $context
    ) {
        parent::__construct($context);
        $this->stateRepository = $stateRepository;
        $this->cityRepository = $cityRepository;
        $this->stateCityRepository = $stateCityRepository;
        $this->cache = $cache;
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getValidationLevel($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'customer/address/fan_courier_validation',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getStates()
    {
        if ($states = $this->getCache('states')) {
            return $states;
        }

        $states = $this->stateRepository->create()->getCollection();

        $optionArray = [];
        foreach ($states->getItems() as $state) {
           $optionArray[] = [
               'value' => $state->getState(),
               'label' => $state->getState()
           ];
        }

        $this->saveCache('states', $optionArray);

        return $optionArray;
    }

    public function getCities()
    {
        if ($cities = $this->getCache('cities')) {
            return $cities;
        }

        $stateCities = $this->stateCityRepository->getCollection();
        $stateCities = $stateCities->getItems();
        $optionArray = [];
        foreach ($stateCities as $stateCity) {
            $optionArray[] = [
                'value' => $stateCity->getCity(),
                'label' => $stateCity->getCity(),
                'state' => $stateCity->getState()
            ];
        }

        $this->saveCache('cities', $optionArray);

        return $optionArray;
    }

    public function getCitiesByState($state)
    {
        if ($cache = $this->getCache($state)) {
            return $cache;
        }

        $citiesByStates = [];
        $cities = $this->getCities();
        foreach ($cities as $city) {
            $citiesByStates[$city["state"]][] = $city["value"];
        }

        foreach ($citiesByStates as $key => $citiesByState) {
            $this->saveCache($key, $citiesByState);
        }

        if (!isset($citiesByStates[$state])) {
            return [];
        }

        return $citiesByStates[$state];
    }

    /**
     * @param $type
     * @return false|mixed
     */
    protected function getCache($type)
    {
        $cache = unserialize($this->cache->load(Validator::IDENTIFIER));
        if (isset($cache[$type])) {
            return $cache[$type];
        }

        return false;
    }

    /**
     * @param $type
     * @param $data
     */
    protected function saveCache($type, $data)
    {
        $cache = unserialize($this->cache->load(Validator::IDENTIFIER));
        $cache[$type] = $data;
        $this->cache->save(
            serialize($cache),
            Validator::IDENTIFIER,
            [Validator::CACHE_TAG]
        );
    }
}