<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\CofidisPayment\Console\Command;

use Oander\CofidisPayment\Helper\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateStatus extends Command
{

    /**
     * @var \Oander\CofidisPayment\Helper\StatusUpdate
     */
    private $statusUpdate;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var \Oander\CofidisPayment\Logger\Logger
     */
    private $logger;

    public function __construct(
        \Oander\CofidisPayment\Helper\Config $config,
        \Oander\CofidisPayment\Helper\StatusUpdate $statusUpdate,
        \Oander\CofidisPayment\Logger\Logger $logger,
        $name = null
    )
    {
        parent::__construct($name);
        $this->statusUpdate = $statusUpdate;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        if($this->config->isCommandLine(0))
        {
            $this->statusUpdate->updateOrdersStatus();
            $output->writeln("Status Updated by CommandLine");
            $this->logger->addDebug("Status Updated by CommandLine");
        }
        else
        {
            $output->writeln("CommandLine execution disabled");
            $this->logger->addDebug("CommandLine execution disabled");
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("oander_cofidispayment:updatestatus");
        $this->setDescription("Update Cofidis statuses");
        parent::configure();
    }
}
