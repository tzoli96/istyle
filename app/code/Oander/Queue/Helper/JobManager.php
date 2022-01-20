<?php

namespace Oander\Queue\Helper;

use Magento\Framework\Exception\LocalizedException;
use Oander\Queue\Enum\Status as StatusEnum;
use Oander\Queue\Model\JobRepository;

class JobManager
{
    /**
     * @var \Oander\Queue\Api\Data\JobFactory
     */
    private $jobFactory;
    /**
     * @var JobRepository
     */
    private $jobRepository;
    /**
     * @var \Oander\Queue\Helper\Queue
     */
    private $queueHelper;

    /**
     * @param \Oander\Queue\Api\Data\JobFactory $jobFactory
     * @param JobRepository $jobRepository
     * @param \Oander\Queue\Helper\Queue $queueHelper
     */
    public function __construct(
        \Oander\Queue\Api\Data\JobFactory $jobFactory,
        \Oander\Queue\Model\JobRepository $jobRepository,
        \Oander\Queue\Helper\Queue $queueHelper
    )
    {
        $this->jobFactory = $jobFactory;
        $this->jobRepository = $jobRepository;
        $this->queueHelper = $queueHelper;
    }

    /**
     * @param $jobClass string
     * @return bool
     */
    static public function validateJobClass(string $jobClass) : bool
    {
        if(class_exists($jobClass)) {
            $parents = class_parents($jobClass);
            if($parents && in_array(\Oander\Queue\Model\JobClass::class, $parents)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $jobClass \Oander\Queue\Model\JobClass
     * @param $executeImmediately bool
     * @return \Oander\Queue\Api\Data\JobInterface
     * @throws LocalizedException
     */
    public function addJobClass($jobClass, bool $executeImmediately = false): \Oander\Queue\Api\Data\JobInterface
    {
        if(self::validateJobClass(get_class($jobClass))) {
            /** @var \Oander\Queue\Api\Data\JobInterface $job */
            $job = $this->jobFactory->create();
            $job->init();
            $job->setClass(get_class($jobClass));
            $job->setData($jobClass->toJson());
            $job->setName($jobClass->getName());
            $this->jobRepository->save($job);
            if ($executeImmediately) {
                $this->queueHelper->runJob($job);
            }
            return $job;
        } else {
            throw new LocalizedException(__("Provided jobClass is not child of " . \Oander\Queue\Model\JobClass::class));
        }
    }
}