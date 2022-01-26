<?php


namespace Oander\Queue\Model\Data;

use Oander\Queue\Api\Data\JobInterface;

class Job extends \Magento\Framework\Api\AbstractExtensibleObject implements JobInterface
{

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
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setJobId($jobId)
    {
        return $this->setData(self::JOB_ID, $jobId);
    }

    /**
     * Get class
     * @return string|null
     */
    public function getJobClass()
    {
        return $this->_get(self::JOBCLASS);
    }

    /**
     * Set class
     * @param string $jobclass
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setJobClass($jobclass)
    {
        return $this->setData(self::JOBCLASS, $jobclass);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Oander\Queue\Api\Data\JobExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Oander\Queue\Api\Data\JobExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Oander\Queue\Api\Data\JobExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get data
     * @return string|null
     */
    public function getAllData()
    {
        return $this->_get(self::ALLDATA);
    }

    /**
     * Set data
     * @param string $data
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setAllData($data)
    {
        return $this->setData(self::ALLDATA, $data);
    }

    /**
     * Get name
     * @return string|null
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Set name
     * @param string $name
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get retries
     * @return string|null
     */
    public function getRetries()
    {
        return $this->_get(self::RETRIES);
    }

    /**
     * Set retries
     * @param string $retries
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setRetries($retries)
    {
        return $this->setData(self::RETRIES, $retries);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
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
     * @return \Oander\Queue\Api\Data\JobInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function init()
    {
        $this->setRetries(0);
        $this->setStatus(\Oander\Queue\Enum\Status::STATUS_INIT);
    }
}
