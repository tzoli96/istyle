<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Enum;

/**
 * Class Config
 *
 * @package Oander\ImoprtM2\Enum
 */
final class Config
{
    const SETTINGS_PATH         = 'oander_import_m2';
    const GENERAL_SETTINGS_PATH = self::SETTINGS_PATH . '/general';

    const GENERAL_DONOR_STORE_VIEW = 'donor_store_view';
    const GENERAL_START            = 'start';
}
