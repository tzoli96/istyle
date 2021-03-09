<?php
namespace Oander\HelloBankPayment\Gateway;


class Config
{
    const HELLOBANK_RESPONSE_STATE_APPROVED              = 1;
    const HELLOBANK_RESPONSE_STATE_FURTHER_REVIEW        = 2;
    const HELLOBANK_RESPONSE_STATE_PRE_APPROVAL          = 4;
    const HELLOBANK_RESPONSE_STATE_CANCELLED             = 5;
    const HELLOBANK_RESPONSE_STATE_REJECTED              = 6;
    const HELLOBANK_RESPONSE_STATE_READY_FOR_SHIPPING    = 20;
    const HELLOBANK_RESPONSE_STATE_WAITING_FOR_DELIVERY  = 22;
    const HELLOBANK_RESPONSE_STATE_DISBURSED             = 28;

    const HELLOBANK_REPONSE_TYPE_KO                      = 1;
    const HELLOBANK_REPONSE_TYPE_OK                      = 2;

    public static $hellobankOrderStatus = [
        1 => 'Approved',
        2 => 'Waiting for Further Review',
        4 => 'Preliminary Approval',
        5 => 'Cancelled by Client',
        6 => 'Rejected',
        20 => 'Ready for Shipping',
        22 => 'Waiting for Delivery',
        28 => 'Disbursed'
    ];
}