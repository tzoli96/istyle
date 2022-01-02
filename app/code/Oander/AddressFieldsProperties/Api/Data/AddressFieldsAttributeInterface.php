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

interface AddressFieldsAttributeInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const ATTRIBUTE_ID = 'attribute_id';
    const STORE_ID = 'store_id';
    const REGEX_PATTERN = 'regex_pattern';
    const DELIMITERS = 'delimiters';
    const VALIDATION_TYPE = 'validation_type';
    const CASE = 'case';
    const NUMBERS_ONLY = 'numbers_only';
    const PREFIX = 'prefix';
    const BLOCKS = 'blocks';
    const PLACEHOLDER = 'placeholder';
    const ERROR_MESSAGE = 'error_message';
    const ADDRESSFIELDSATTRIBUTE_ID = 'addressfieldsattribute_id';
    const STRING_LENGTH = 'string_length';
    const ENABLE_FORMATTING = 'enable_formatting';

    /**
     * Get addressfieldsattribute_id
     * @return string|null
     */
    public function getAddressfieldsattributeId();

    /**
     * Set addressfieldsattribute_id
     * @param string $addressfieldsattributeId
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setAddressfieldsattributeId($addressfieldsattributeId);

    /**
     * Get placeholder
     * @return string|null
     */
    public function getPlaceholder();

    /**
     * Set placeholder
     * @param string $placeholder
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setPlaceholder($placeholder);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeExtensionInterface $extensionAttributes
    );

    /**
     * Get enable_formatting
     * @return string|null
     */
    public function getEnableFormatting();

    /**
     * Set enable_formatting
     * @param string $enableFormatting
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setEnableFormatting($enableFormatting);

    /**
     * Get blocks
     * @return string|null
     */
    public function getBlocks();

    /**
     * Set blocks
     * @param string $blocks
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setBlocks($blocks);

    /**
     * Get delimiters
     * @return string|null
     */
    public function getDelimiters();

    /**
     * Set delimiters
     * @param string $delimiters
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setDelimiters($delimiters);

    /**
     * Get prefix
     * @return string|null
     */
    public function getPrefix();

    /**
     * Set prefix
     * @param string $prefix
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setPrefix($prefix);

    /**
     * Get numbers_only
     * @return string|null
     */
    public function getNumbersOnly();

    /**
     * Set numbers_only
     * @param string $numbersOnly
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setNumbersOnly($numbersOnly);

    /**
     * Get case
     * @return string|null
     */
    public function getCase();

    /**
     * Set case
     * @param string $case
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setCase($case);

    /**
     * Get validation type
     * @return string|null
     */
    public function getValidationType();

    /**
     * Set validation type
     * @param string $validationType
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setValidationType($validationType);

    /**
     * Get string_length
     * @return string|null
     */
    public function getStringLength();

    /**
     * Set string_length
     * @param string $stringLength
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setStringLength($stringLength);

    /**
     * Get regex_pattern
     * @return string|null
     */
    public function getRegexPattern();

    /**
     * Set regex_pattern
     * @param string $regexPattern
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setRegexPattern($regexPattern);

    /**
     * Get error_message
     * @return string|null
     */
    public function getErrorMessage();

    /**
     * Set error_message
     * @param string $errorMessage
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setErrorMessage($errorMessage);

    /**
     * Get attribute_id
     * @return string|null
     */
    public function getAttributeId();

    /**
     * Set attribute_id
     * @param string $attributeId
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setAttributeId($attributeId);

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param string $storeId
     * @return \Oander\AddressFieldsProperties\Api\Data\AddressFieldsAttributeInterface
     */
    public function setStoreId($storeId);
}
