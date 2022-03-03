<?php
/**
 * Provide API for address lists
 * Copyright (C) 2019
 *
 * This file is part of Oander/AddressListAPI.
 *
 * Oander/AddressListAPI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

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