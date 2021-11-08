<?php

namespace Oney\ThreeByFour\Logger;

use Oney\ThreeByFour\Helper\Config;

class Logger extends \Monolog\Logger
{
    /**
     * @var Config
     */
    protected $_config;

    /**
     * Logger constructor.
     *
     * @param Config $config
     * @param string $name
     * @param array  $handlers
     * @param array  $processors
     */
    public function __construct(
        Config $config,
        string $name,
        array $handlers = array(),
        array $processors = array()
    )
    {
        $this->_config = $config;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * @param string $string
     * @param array  $context
     *
     * @return bool|void
     */
    public function info($string = "", array $context = array()) {
        if($this->_config->getGeneralConfigValue('log_active')) {
            parent::info($string, $context);
        }
    }
}
