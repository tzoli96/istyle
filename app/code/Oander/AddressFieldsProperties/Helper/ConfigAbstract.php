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

namespace Oander\AddressFieldsProperties\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

abstract class ConfigAbstract {

    CONST CONFIG_BASE_PATH = "oander_address_fields_properties";

    CONST CONFIG_PLACEHOLDER        = "placeholder";
    CONST CONFIG_ENABLE_FORMATTING  = "enable_formatting";
    CONST CONFIG_BLOCKS             = "blocks";
    CONST CONFIG_DELIMITERS         = "delimiters";
    CONST CONFIG_PREFIX             = "prefix";
    CONST CONFIG_NUMBERS_ONLY       = "numbers_only";
    CONST CONFIG_CASE               = "case";
    CONST CONFIG_VALIDATION_TYPE    = "validation_type";
    CONST CONFIG_STRING_LENGTH      = "string_length";
    CONST CONFIG_REGEX_PATTERN      = "regex_pattern";
    CONST CONFIG_ERROR_MESSAGE      = "error_message";

    CONST BASE_VALUES = [
        self::CONFIG_PLACEHOLDER => null,
        self::CONFIG_ENABLE_FORMATTING => 0,
        self::CONFIG_BLOCKS => null,
        self::CONFIG_DELIMITERS => null,
        self::CONFIG_PREFIX => null,
        self::CONFIG_NUMBERS_ONLY => 0,
        self::CONFIG_CASE => 0,
        self::CONFIG_VALIDATION_TYPE => 0,
        self::CONFIG_STRING_LENGTH => null,
        self::CONFIG_REGEX_PATTERN => [],
        self::CONFIG_ERROR_MESSAGE => null
    ];

    /**
     * @return array
     */
    public function getBaseConfig()
    {
        return self::BASE_VALUES;
    }

    /**
     * @return array
     */
    public function getBaseConfigWithDefault()
    {
        $baseConfig = $this->getBaseConfig();
        $defaults = [];
        foreach ($baseConfig as $key => $value)
        {
            $defaults[$key] = true;
        }
        $baseConfig["use_default"] = $defaults;
        return $baseConfig;
    }

    /**
     * @param string $string
     * @param string $endString
     * @return bool
     */
    protected function endsWith($string, $endString)
    {
        $len = strlen($endString);
        if ($len == 0) {
            return true;
        }
        return (substr($string, -$len) === $endString);
    }
}