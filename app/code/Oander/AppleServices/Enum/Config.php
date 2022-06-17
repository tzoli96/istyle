<?php
namespace Oander\AppleServices\Enum;

use Oander\Base\Enum\BaseEnum;

/**
 * Class ConfigEnum
 *
 * @package Oander\SalesforceWidget\Enum
 */
final class Config extends BaseEnum
{
    const SYSTEM_CONFIG_PATH    = 'oander_apple_services/';
    const GENERAL_PATH          = self::SYSTEM_CONFIG_PATH . 'general';
    const MUSIC_PATH            = self::SYSTEM_CONFIG_PATH . 'music';
    const TV_PATH               = self::SYSTEM_CONFIG_PATH . 'tv';
    const ARCADE_PATH           = self::SYSTEM_CONFIG_PATH . 'arcade';
    const ICLOUD_PATH           = self::SYSTEM_CONFIG_PATH . 'icloud';

    const GENERAL_TEST_MODE         = 'test_mode';
    const GENERAL_ENABLED           = 'enabled';
    const GENERAL_COOKIE_LIFETIME   = 'cookie_lifetime';
    const GENERAL_CAPTCHA_KEY       = 'google_invisible_key';
    const GENERAL_ENDPOINT          = 'endpoint';
    const GENERAL_UNIQUE_ID         = 'unique';
    const GENERAL_SECRET_KEY        = 'secret_key';
}
