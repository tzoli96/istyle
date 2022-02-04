<?php
/**
 * Oander_DisabledProductPage
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\DisabledProductPage\Enum;

/**
 * Class Config
 * @package Oander\DisabledProductPage\Enum
 */
final class Config
{
    const SETTINGS_PATH = 'oander_disabled_product_page';
    const GENERAL_SETTINGS_PATH = self::SETTINGS_PATH . '/general';

    const GENERAL_SETTINGS_ENABLED = 'enabled';
    const GENERAL_SETTINGS_SUBSTITUTE_PRODUCTS_TITLE = 'substitute_products_title';
    const GENERAL_SETTINGS_OUT_OF_STOCK_TEXT = 'out_of_stock_text';
    const GENERAL_SETTINGS_INDEXING_RULE = 'indexing_rule';
}