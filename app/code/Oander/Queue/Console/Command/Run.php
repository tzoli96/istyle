<?php


namespace Oander\Queue\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Oander\Queue\Model\Config\Source\RunMethod;

class Run extends Command
{
    /**
     * @var \Oander\Queue\Helper\Config
     */
    private $configHelper;
    /**
     * @var \Oander\Queue\Helper\Queue
     */
    private $queueHelper;

    /**
     * @param \Oander\Queue\Helper\Config $configHelper
     * @param \Oander\Queue\Helper\Queue $queueHelper
     * @param $name
     */
    public function __construct(
        \Oander\Queue\Helper\Config $configHelper,
        \Oander\Queue\Helper\Queue $queueHelper,
        $name = null
    )
    {
        parent::__construct($name);
        $this->configHelper = $configHelper;
        $this->queueHelper = $queueHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        if($this->configHelper->isEnabled() && $this->configHelper->getMethod() == RunMethod::RUN_METHOD_CONSOLE)
        {
            $executedJobsCnt = $this->queueHelper->Run();
            $output->writeln("Queue executed " . $executedJobsCnt . " jobs");
        }
        else
        {
            $output->writeln("CLI execution is disabled");
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("oander_queue:run");
        $this->setDescription("Run queue");
        parent::configure();
    }
}
