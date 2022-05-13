<?php
namespace Oander\ExternalRoundingUnit\Enum;

use Oander\Base\Enum\BaseEnum;

final class Config extends BaseEnum
{
    const SYSTEM_CONFIG_PATH        = 'oander_external_rounding_unit/';
    const GENERAL_PATH              = self::SYSTEM_CONFIG_PATH . 'general';

    const GENERAL_ENABLED           = 'enabled';
    const ROUNDING_RULE             = 'rounding_rule';
}
