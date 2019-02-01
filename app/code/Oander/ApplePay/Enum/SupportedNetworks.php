<?php

namespace Oander\ApplePay\Enum;


class SupportedNetworks
{
    const amex = 'amex';
    const cartesBancaires = 'cartesBancaires';
    const chinaUnionPay = 'chinaUnionPay';
    const discover = 'discover';
    const eftpos = 'eftpos';
    const electron = 'electron';
    const interac = 'interac';
    const jcb = 'jcb';
    const maestro = 'maestro';
    const masterCard = 'masterCard';
    const privateLabel = 'privateLabel';
    const visa = 'visa';
    const vPay = 'vPay';
    const firstavailable = [
        'amex' => 1,
        'cartesBancaires' => 4,
        'chinaUnionPay' => 1,
        'discover' => 1,
        'eftpos' => 4,
        'electron' => 4,
        'interac' => 1,
        'jcb' => 2,
        'maestro' => 4,
        'masterCard' => 1,
        'privateLabel' => 1,
        'visa' => 1,
        'vPay' => 4
        ];
}