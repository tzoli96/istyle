<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Oander\FanCourierValidator\Api\StateRepositoryInterface;
use Oander\FanCourierValidator\Api\StateCityRepositoryInterface;
use Oander\FanCourierValidator\Api\CityRepositoryInterface;

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

    public function __construct(
        StateRepositoryInterface $stateRepository,
        CityRepositoryInterface $cityRepository,
        StateCityRepositoryInterface $stateCityRepository,
        Context $context
    ) {
        parent::__construct($context);
        $this->stateRepository = $stateRepository;
        $this->cityRepository = $cityRepository;
        $this->stateCityRepository = $stateCityRepository;
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
       $states = $this->stateRepository->create()->getCollection();

       $optionArray = [];
       foreach ($states->getItems() as $state) {
           $optionArray[] = [
               'value' => $state->getState(),
               'label' => $state->getState()
           ];
       }

       return $optionArray;
    }

    public function getCities()
    {
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

        return $optionArray;
    }

}