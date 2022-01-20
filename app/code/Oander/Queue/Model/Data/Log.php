<?php


namespace Oander\Queue\Model\Data;

use Oander\Queue\Api\Data\LogInterface;

class Log extends \Magento\Framework\Api\AbstractExtensibleObject implements LogInterface
{

    /**
     * Get log_id
     * @return string|null
     */
    public function getLogId()
    {
        return $this->_get(self::LOG_ID);
    }

    /**
     * Set log_id
     * @param string $logId
     * @return \Oander\Queue\Api\Data\LogInterface
     */
    public function setLogId($logId)
    {
        return $this->setData(self::LOG_ID, $logId);
    }

    /**
     * Get job_id
     * @return string|null
     */
    public function getJobId()
    {
        return $this->_get(self::JOB_ID);
    }

    /**
     * Set job_id
     * @param string $jobId
     * @return \Oander\Queue\Api\Data\LogInterface
     */
    public function setJobId($jobId)
    {
        return $this->setData(self::JOB_ID, $jobId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Oander\Queue\Api\Data\LogExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Oander\Queue\Api\Data\LogExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Oander\Queue\Api\Data\LogExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get input
     * @return string|null
     */
    public function getInput()
    {
        return $this->_get(self::INPUT);
    }

    /**
     * Set input
     * @param string $input
     * @return \Oander\Queue\Api\Data\LogInterface
     */
    public function setInput($input) : LogInterface
    {
        return $this->setData(self::INPUT, $input);
    }

    /**
     * Get output
     * @return string|null
     */
    public function getOutput()
    {
        return $this->_get(self::OUTPUT);
    }

    /**
     * Set output
     * @param string $output
     * @return \Oander\Queue\Api\Data\LogInterface
     */
    public function setOutput($output) : LogInterface
    {
        return $this->setData(self::OUTPUT, $output);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Oander\Queue\Api\Data\LogInterface
     */
    public function setCreatedAt($createdAt) : LogInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
