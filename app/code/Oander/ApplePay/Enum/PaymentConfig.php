<?php

declare(strict_types=1);

namespace Oander\ApplePay\Enum;

/**
 * Class Config
 * @package Oander\ApplePay\Enum
 */
final class PaymentConfig
{
    const PAYMENT_SETTINGS_PATH              = 'payment/applepay';
    const PAYMENT_SETTINGS_ACTIVE      = 'active';
    const PAYMENT_SETTINGS_MERCHANT_ID     = 'merchant_id';
    const PAYMENT_SETTINGS_TITLE     = 'title';
    const PAYMENT_SETTINGS_SHOW_PRODUCT_PAGE     = 'show_product_page';
    const PAYMENT_SETTINGS_SHOW_MINI_CART     = 'show_mini_cart';
    const PAYMENT_SETTINGS_SHOW_CART     = 'show_cart';
    const PAYMENT_SETTINGS_SHOW_CHECKOUT     = 'show_checkout';
    const PAYMENT_SETTINGS_ENABLED_SHIPPING_METHODS     = 'enabled_shipping_methods';
    const PAYMENT_SETTINGS_ALLOWSPECIFIC     = 'allowspecific';
    const PAYMENT_SETTINGS_SPECIFICCOUNTRY     = 'specificcountry';
    const PAYMENT_SETTINGS_MERCHANT_CAPABILITIES     = 'merchant_capabilities';
    const PAYMENT_SETTINGS_SUPPORTED_NETWORKS     = 'supported_networks';
    const PAYMENT_SETTINGS_MERCHANT_NAME     = 'merchant_name';
    const PAYMENT_SETTINGS_PAYMENT_ACTION    = 'payment_action';

    const PAYMENT_SETTINGS_PATH_ACTIVE      = self::PAYMENT_SETTINGS_PATH . '/' . self::PAYMENT_SETTINGS_ACTIVE;
    const PAYMENT_SETTINGS_PATH_MERCHANT_ID     = self::PAYMENT_SETTINGS_PATH . '/'. self::PAYMENT_SETTINGS_MERCHANT_ID;
    const PAYMENT_SETTINGS_PATH_TITLE     = self::PAYMENT_SETTINGS_PATH . '/' . self::PAYMENT_SETTINGS_TITLE;
    const PAYMENT_SETTINGS_PATH_SHOW_PRODUCT_PAGE     = self::PAYMENT_SETTINGS_PATH . '/' . self::PAYMENT_SETTINGS_SHOW_PRODUCT_PAGE;
    const PAYMENT_SETTINGS_PATH_SHOW_MINI_CART     = self::PAYMENT_SETTINGS_PATH . '/' . self::PAYMENT_SETTINGS_SHOW_MINI_CART;
    const PAYMENT_SETTINGS_PATH_SHOW_CART     = self::PAYMENT_SETTINGS_PATH . '/' . self::PAYMENT_SETTINGS_SHOW_CART;
    const PAYMENT_SETTINGS_PATH_SHOW_CHECKOUT     = self::PAYMENT_SETTINGS_PATH . '/' . self::PAYMENT_SETTINGS_SHOW_CHECKOUT;
    const PAYMENT_SETTINGS_PATH_ENABLED_SHIPPING_METHODS     = self::PAYMENT_SETTINGS_PATH . '/'. self::PAYMENT_SETTINGS_ENABLED_SHIPPING_METHODS;
    const PAYMENT_SETTINGS_PATH_ALLOWSPECIFIC     = self::PAYMENT_SETTINGS_PATH . '/' . self::PAYMENT_SETTINGS_ALLOWSPECIFIC;
    const PAYMENT_SETTINGS_PATH_SPECIFICCOUNTRY     = self::PAYMENT_SETTINGS_PATH . '/' . self::PAYMENT_SETTINGS_SPECIFICCOUNTRY;
    const PAYMENT_SETTINGS_PATH_MERCHANT_CAPABILITIES     = self::PAYMENT_SETTINGS_PATH . '/' . self::PAYMENT_SETTINGS_MERCHANT_CAPABILITIES;
    const PAYMENT_SETTINGS_PATH_SUPPORTED_NETWORKS     = self::PAYMENT_SETTINGS_PATH . '/'. self::PAYMENT_SETTINGS_SUPPORTED_NETWORKS;
}