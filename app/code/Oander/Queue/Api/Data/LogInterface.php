<?php


namespace Oander\Queue\Api\Data;

interface LogInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const TABLE = 'oander_queue_log';
    const JOB_ID = 'job_id';
    const LOG_ID = 'log_id';
    const CREATED_AT = 'created_at';
    const INPUT = 'input';
    const OUTPUT = 'output';

    /**
     * Get log_id
     * @return string|null
     */
    public function getLogId();

    /**
     * Set log_id
     * @param string $logId
     * @return \Oander\Queue\Api\Data\LogInterface
     */
    public function setLogId($logId);

    /**
     * Get job_id
     * @return string|null
     */
    public function getJobId();

    /**
     * Set job_id
     * @param string $jobId
     * @return \Oander\Queue\Api\Data\LogInterface
     */
    public function setJobId($jobId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Oander\Queue\Api\Data\LogExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Oander\Queue\Api\Data\LogExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Oander\Queue\Api\Data\LogExtensionInterface $extensionAttributes
    );

    /**
     * Get input
     * @return string|null
     */
    public function getInput();

    /**
     * Set input
     * @param string $input
     * @return \Oander\Queue\Api\Data\LogInterface
     */
    public function setInput($input): LogInterface;

    /**
     * Get output
     * @return string|null
     */
    public function getOutput();

    /**
     * Set output
     * @param string $output
     * @return \Oander\Queue\Api\Data\LogInterface
     */
    public function setOutput($output): LogInterface;

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Oander\Queue\Api\Data\LogInterface
     */
    public function setCreatedAt($createdAt): LogInterface;
}
