<?php

namespace Oander\CustomerAddressValidation\Enum;


class Config
{
    const ADDRESS_PATH      = 'customer/address/';
    const AUTOFILL_ENABLED  = self::ADDRESS_PATH . 'zipcity_autofill_enabled';
    const SYNC_MODE       = self::ADDRESS_PATH . 'zipcity_sync_mode';
}