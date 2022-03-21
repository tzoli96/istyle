<?php

namespace Oander\SalesforceLoyalty\Enum;

final class CustomerAttribute
{
    const REGISTER_TO_LOYALTY           = 'register_to_loyalty';
    const REGISTRED_TO_LOYALTY          = 'registred_to_loyalty';

    //Loyalty Registration status
    const REGISTRATION_STATUS_START     = 0;
    const REGISTRATION_STATUS_WAITING   = 1;
    const REGISTRATION_STATUS_DONE      = 2;
}