<?php

namespace Oander\CofidisPayment\Enum;

use Oander\Base\Enum\BaseEnum;

/**
 * Class Config
 * @package Oander\CofidisPayment\Enum
 */
class Ownshare extends BaseEnum
{
    const CONSTRUCTION_GROUP    = 'grp';
    const NAME                  = 'name';
    const PRIORITY              = 'prio';
    const INSTALMENTS           = 'inst';
    const MINIMUM_LOAN          = 'min';
    const MAXIMUM_LOAN          = 'max';
    const OWNSHARE_PRICE_LIMIT  = 'limit';
    const OWNSHARE_PERCENTAGE   = 'perc';
}