<?php

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
        $return = self::BASE_VALUES;
        $return[self::CONFIG_ERROR_MESSAGE] = __("Wrong value in field");
        return $return;
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