<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger as MonoLogLogger;

/**
 * Class Handler
 *
 * @package Oander\IstyleCustomization\Logger
 */
class Handler extends Base
{
    protected $loggerType = MonoLogLogger::INFO;

    protected $fileName = '/var/log/oander/session.log';
}