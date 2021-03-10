<?php
namespace Oander\HelloBankPayment\Enum;

final class Request
{
    const ACTION_BAREM_GRID_INDEX       = 'hellobank/barem/index';
    const ACTION_BAREM_FORM_SAVE        = 'hellobank/barem/save';
    const URL_DELETE_PATH               = 'hellobank/barem/delete';
    const URL_EDIT_PATH                 = 'hellobank/barem/edit';

    const PARAM_BACK                    = 'back';

    const PAYMENT_PROCCESSING_ACTION    = "hellobank/payment/processing/";
}