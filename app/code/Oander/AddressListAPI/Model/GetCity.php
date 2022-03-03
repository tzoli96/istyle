<?php

namespace Oander\AddressListAPI\Model;

use Oander\AddressListAPI\Api\GetCityInterface;

class GetCity implements GetCityInterface
{
    /**
     * Get country path
     */
    const COUNTRY_CODE_PATH = 'general/country/default';


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->resource = $resource;
    }

    /**
     * @inehritdoc
     */
    public function getAllCity(): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(\Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::TABLE, \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::CITY)
            ->where(\Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::COUNTRY_CODE . '=?', $this->_getCountryCode());
        return $connection->fetchCol($select);
    }

    /**
     * @inehritdoc
     */
    public function getAllRegion(): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(\Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::TABLE, \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::REGION)
            ->where(\Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::COUNTRY_CODE . '=?', $this->_getCountryCode())
            ->group(\Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::REGION);
        return $connection->fetchCol($select);
    }

    /**
     * @inehritdoc
     */
    public function getByRegion($region): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(\Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::TABLE, \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::CITY)
            ->where(\Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::COUNTRY_CODE . '=?', $this->_getCountryCode())
            ->where(\Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::REGION . '=?', $region);
        return $connection->fetchCol($select);
    }

    private function _getCountryCode()
    {
        return $this->scopeConfig->getValue(
            self::COUNTRY_CODE_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}