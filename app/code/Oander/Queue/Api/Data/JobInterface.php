<?php


namespace Oander\Queue\Api\Data;

interface JobInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const TABLE = 'oander_queue_job';
    const RETRIES = 'retries';
    const JOB_ID = 'job_id';
    const STATUS = 'status';
    const ALLDATA = 'all_data';
    const NAME = 'name';
    const JOBCLASS = 'job_class';
    const CREATED_AT = 'created_at';

    /**
     * Get job_id
     * @return string|null
     */
    public function getJobId();

    /**
     * Set job_id
     * @param string $jobId
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setJobId($jobId);

    /**
     * Get jobclass
     * @return string|null
     */
    public function getJobClass();

    /**
     * Set jobclass
     * @param string $jobclass
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setJobClass($jobclass);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Oander\Queue\Api\Data\JobExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Oander\Queue\Api\Data\JobExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Oander\Queue\Api\Data\JobExtensionInterface $extensionAttributes
    );

    /**
     * Get alldata
     * @return string|null
     */
    public function getAllData();

    /**
     * Set alldata
     * @param string $alldata
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setAllData($alldata);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setName($name);

    /**
     * Get retries
     * @return string|null
     */
    public function getRetries();

    /**
     * Set retries
     * @param string $retries
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setRetries($retries);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setStatus($status);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Initialize Class
     * @return mixed
     */
    public function init();
}
