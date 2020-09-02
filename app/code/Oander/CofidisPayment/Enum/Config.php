<?php

namespace Oander\CofidisPayment\Enum;

use Oander\Base\Enum\BaseEnum;

/**
 * Class Config
 * @package Oander\CofidisPayment\Enum
 */
class Config extends BaseEnum
{
    const PAYMENT_PATH      = 'payment/cofidis';
    const ACTIVE            = 'active';
    const COMMANDLINE       = 'commandline';
    const INSTRUCTIONS      = 'instructions';
    const ENVIRONMENT       = 'environment';
    const SHOP_ID           = 'shop_id';
    const IV_CODE           = 'iv_code';
    const KEY               = 'key';
    const CONSTRUCTION_GROUP = 'construction_group';
    const OWNSHARES         = 'ownshares';
    const OWNSHARES_TEST    = 'ownshares_test';
    const STATUS_URL        = 'status_url';
    const TERMS_URL         = 'termsandcondition_url';
    const SHOP_ID_TEST      = 'shop_id_test';
    const IV_CODE_TEST      = 'iv_code_test';
    const KEY_TEST          = 'key_test';
    const CONSTRUCTION_GROUP_TEST = 'construction_group_test';
    const STATUS_URL_TEST   = 'status_url_test';
}