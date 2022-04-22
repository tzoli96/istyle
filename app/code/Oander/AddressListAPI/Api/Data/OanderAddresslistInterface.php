<?php

namespace Oander\AddressListAPI\Api\Data;

interface OanderAddresslistInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const TABLE = 'oander_addresslist';
    const COUNTRY_CODE = 'country_code';
    const REGION = 'region';
    const CITY = 'city';

    /**
     * Get country_code
     * @return string|null
     */
    public function getCountryCode();

    /**
     * Set country_code
     * @param string $countryCode
     * @return \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface
     */
    public function setCountryCode($countryCode);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Oander\AddressListAPI\Api\Data\OanderAddresslistExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Oander\AddressListAPI\Api\Data\OanderAddresslistExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Oander\AddressListAPI\Api\Data\OanderAddresslistExtensionInterface $extensionAttributes
    );

    /**
     * Get region
     * @return string|null
     */
    public function getRegion();

    /**
     * Set region
     * @param string $region
     * @return \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface
     */
    public function setRegion($region);

    /**
     * Get city
     * @return string|null
     */
    public function getCity();

    /**
     * Set city
     * @param string $city
     * @return \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface
     */
    public function setCity($city);
}