<?php
/**
 * Oander_DisabledProductPage
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\DisabledProductPage\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger as MonoLogLogger;

/**
 * Class Handler
 *
 * @package Oander\DisabledProductPage\Logger
 */
class Handler extends Base
{
    protected $loggerType = MonoLogLogger::INFO;

    protected $fileName = '/var/log/oander/disabled_product_page.log';
}