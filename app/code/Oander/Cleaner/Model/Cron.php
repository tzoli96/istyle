<?php

namespace Oander\Cleaner\Model;

use Oander\Cleaner\Model\Service\Execute;

class Cron
{
    /**
     * @var Execute
     */
    private $execute;

    public function __construct(
        Execute $execute
    ){
        $this->execute = $execute;
    }

    public function execute()
    {
        $this->execute->execute();
    }

}