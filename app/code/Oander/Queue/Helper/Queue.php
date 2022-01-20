<?php

namespace Oander\Queue\Helper;

use Magento\Framework\Profiler\Driver\Standard\Stat;
use \Oander\Queue\Enum\Status as StatusEnum;

class Queue
{
    /**
     * @var \Oander\Queue\Model\ResourceModel\Job\CollectionFactory
     */
    private $jobCollectionFactory;
    /**
     * @var \Oander\Queue\Api\Data\LogFactory
     */
    private $logFactory;
    /**
     * @var \Oander\Queue\Model\LogRepository
     */
    private $logRepository;
    /**
     * @var \Oander\Queue\Model\JobRepository
     */
    private $jobRepository;

    /**
     * @param \Oander\Queue\Model\ResourceModel\Job\CollectionFactory $jobCollectionFactory
     * @param \Oander\Queue\Model\JobRepository $jobRepository
     * @param \Oander\Queue\Model\LogRepository $logRepository
     * @param \Oander\Queue\Api\Data\LogFactory $logFactory
     */
    public function __construct(
        \Oander\Queue\Model\ResourceModel\Job\CollectionFactory $jobCollectionFactory,
        \Oander\Queue\Model\JobRepository $jobRepository,
        \Oander\Queue\Model\LogRepository $logRepository,
        \Oander\Queue\Api\Data\LogFactory $logFactory
    )
    {
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->logFactory = $logFactory;
        $this->logRepository = $logRepository;
        $this->jobRepository = $jobRepository;
    }

    /**
     * @return int
     */
    public function Run()
    {
        /** @var \Oander\Queue\Model\ResourceModel\Job\Collection $jobCollection */
        $jobCollection = $this->jobCollectionFactory->create();
        $jobCollection->addFieldToFilter([
            \Oander\Queue\Api\Data\JobInterface::STATUS => [
                "in" => \Oander\Queue\Enum\Status::getActiveStatuses()
            ]
        ]);
        /** @var \Oander\Queue\Api\Data\JobInterface $job */
        foreach ($jobCollection as $job)
        {
            $this->runJob($job);
        }
        return $jobCollection->getSize();
    }

    /**
     * @param \Oander\Queue\Api\Data\JobInterface $job
     * @return bool
     */
    public function runJob($job)
    {
        if(JobManager::validateJobClass($job->getClass()))
        {
            $this->_runJob($job);
        }
    }

    /**
     * @param \Oander\Queue\Api\Data\JobInterface $job
     * @return bool
     */
    private function _runJob($job)
    {
        $class = $this->_getClass($job->getClass());
        $class->clear();
        $class->setData(\Zend_Json::decode($job->getData()));
        /** @var \Oander\Queue\Api\Data\LogInterface $log */
        $log = $this->logFactory->create();
        try{
            //PREPROCESS
            $log->setJobId($job->getJobId());
            if($class->getInput())
                $log->setInput($class->getInput());
            $this->logRepository->save($log);

            //PROCESS
            $result = $class->execute();

            //POSTPROCESS
            if($class->getOutput())
                $log->setOutput($class->getOutput());
            $this->logRepository->save($log);

            //HANDLE OUTPUT
            $job->setStatus(StatusEnum::STATUS_INPROGRESS);
            $job->setData($class->toJson());
            $job->setRetries($job->getRetries() + 1);
            if($class->hasError()) //HAS ERROR
            {
                $job->setStatus(StatusEnum::STATUS_ERROR);
            } elseif ($result) { //SUCCESSFULLY EXECUTED
                $job->setStatus(StatusEnum::STATUS_CLOSED);
            } else { //NEED TO DO NEXT CYCLE
                if ($class->getRetriesCount() <= $job->getRetries()) { //REACHED RETRY LIMIT
                    $job->setStatus(StatusEnum::STATUS_MAXRETRYREACHED);
                }
            }
            $this->jobRepository->save($job);
        }
        catch (\Exception $e)
        {
            $job->setStatus(StatusEnum::STATUS_ERROR);
            if($log->getLogId())
                $log = $this->logFactory->create();
            $log->setJobId($job->getJobId());
            $log->setOutput($e->getMessage());
            $this->logRepository->save($log);
            $this->jobRepository->save($job);
        }
    }

    /**
     * @param $className
     * @return \Oander\Queue\Model\JobClass
     */
    private function _getClass($className)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get($className);
    }
}