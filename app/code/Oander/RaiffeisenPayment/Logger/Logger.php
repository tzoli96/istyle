<?php

namespace Oander\RaiffeisenPayment\Logger;

use Oander\RaiffeisenPayment\Helper\Config;
use Monolog\Logger as Subject;

class Logger extends Subject
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Logger constructor.
     *
     * @param Config $config
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        Config $config,
        string $name,
        array  $handlers = array(),
        array  $processors = array()
    )
    {
        $this->config = $config;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * @param string $string
     * @param array $context
     *
     * @return bool|void
     */
    public function info($string = "", array $context = array())
    {
        if ($this->config->getLoggerIsActive()) {
            parent::info($string, $context);
        }
    }
}
