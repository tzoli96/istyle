<?php
/**
 * Address Fields Properties
 * Copyright (C) 2019 
 * 
 * This file is part of Oander/AddressFieldsProperties.
 * 
 * Oander/AddressFieldsProperties is free software: you can redistribute it and/or modify
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

namespace Oander\AddressFieldsProperties\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface AddressFieldsAttributeRepositoryInterface
{

    /**
     * Save addressFieldsAttribute
     * @param \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface $addressFieldsAttribute
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface $addressFieldsAttribute
    );

    /**
     * Retrieve addressFieldsAttribute
     * @param string $addressfieldsattributeId
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($addressfieldsattributeId);

    /**
     * Retrieve addressFieldsAttribute matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete addressFieldsAttribute
     * @param \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface $addressFieldsAttribute
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface $addressFieldsAttribute
    );

    /**
     * Delete addressFieldsAttribute by ID
     * @param string $addressfieldsattributeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($addressfieldsattributeId);
}
