<?php


namespace Oander\Queue\Cron;

use Oander\Queue\Model\Config\Source\RunMethod;

class Queue
{

    protected $logger;
    /**
     * @var \Oander\Queue\Helper\Config
     */
    private $configHelper;
    /**
     * @var \Oander\Queue\Helper\Queue
     */
    private $queueHelper;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Oander\Queue\Helper\Config $configHelper
     * @param \Oander\Queue\Helper\Queue $queueHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Oander\Queue\Helper\Config $configHelper,
        \Oander\Queue\Helper\Queue $queueHelper
    )
    {
        $this->logger = $logger;
        $this->configHelper = $configHelper;
        $this->queueHelper = $queueHelper;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        if($this->configHelper->isEnabled() && $this->configHelper->getMethod() == RunMethod::RUN_METHOD_CRON)
        {
            $executedJobsCnt = $this->queueHelper->Run();
            $this->logger->addInfo("Queue executed " . $executedJobsCnt . " jobs");
        }
    }
}
