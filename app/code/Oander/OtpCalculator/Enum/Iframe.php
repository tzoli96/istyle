<?php

namespace Oander\OtpCalculator\Enum;
/**
 * Class ProductTypeEnum
 *
 * @package Oander\IstyleCustomization\Enum
 */
final class Iframe
{
    const URL = 'https://aruhitel.otpbank.hu/webshop/webshop-calculator.html';
    const PURCHASE_PRICE = 'purchasePrice';
    const CONSTRUCTION_GROUP = 'constructionGroup';
    const RETAILER_ID = 'retailerId';
    const URL_WITH_PARAMS = 'https://aruhitel.otpbank.hu/webshop/webshop-calculator.html?'.self::PURCHASE_PRICE.'=%s&'.self::CONSTRUCTION_GROUP.'=%s&'.self::RETAILER_ID.'=%s';
}