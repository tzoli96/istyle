<?php
/**
 * Loan Payment modul for Cofidis
 * Copyright (C) 2019 
 * 
 * This file included in Oander/CofidisPayment is licensed under OSL 3.0
 * 
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Oander\CofidisPayment\Cron;

class StatusUpdate
{

    protected $logger;
    /**
     * @var \Oander\CofidisPayment\Helper\Config
     */
    private $config;
    /**
     * @var \Oander\CofidisPayment\Helper\StatusUpdate
     */
    private $statusUpdate;

    /**
     * Constructor
     *
     * @param \Oander\CofidisPayment\Logger\Logger $logger
     */
    public function __construct(
        \Oander\CofidisPayment\Logger\Logger $logger,
        \Oander\CofidisPayment\Helper\Config $config,
        \Oander\CofidisPayment\Helper\StatusUpdate $statusUpdate
    )
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->statusUpdate = $statusUpdate;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        if(!$this->config->isCommandLine(0)) {
            $this->statusUpdate->updateOrdersStatus();
            $this->logger->addDebug("Status Updated by Cron");
        }
        else
        {
            $this->logger->addDebug("Cron execution disabled");
        }
    }
}
