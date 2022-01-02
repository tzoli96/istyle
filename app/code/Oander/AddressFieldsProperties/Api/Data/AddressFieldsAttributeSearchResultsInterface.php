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

namespace Oander\AddressFieldsProperties\Api\Data;

interface AddressFieldsAttributeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get addressFieldsAttribute list.
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface[]
     */
    public function getItems();

    /**
     * Set placeholder list.
     * @param \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
