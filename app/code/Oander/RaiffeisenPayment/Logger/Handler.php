<?php
namespace Oander\RaiffeisenPayment\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    protected $loggerType = Logger::INFO;
    protected $fileName = '/var/log/oander_raiffeisen_error.log';
}
