<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger as MonoLogLogger;

/**
 * Class Handler
 *
 * @package Oander\ImportM2\Logger
 */
class Handler extends Base
{
    protected $loggerType = MonoLogLogger::INFO;

    protected $fileName = '/var/log/oander/import_m2.log';
}
