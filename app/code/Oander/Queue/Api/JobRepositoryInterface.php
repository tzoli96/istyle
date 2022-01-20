<?php


namespace Oander\Queue\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface JobRepositoryInterface
{

    /**
     * Save Job
     * @param \Oander\Queue\Api\Data\JobInterface $job
     * @return \Oander\Queue\Api\Data\JobInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Oander\Queue\Api\Data\JobInterface $job
    );

    /**
     * Retrieve Job
     * @param string $jobId
     * @return \Oander\Queue\Api\Data\JobInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($jobId);

    /**
     * Retrieve Job matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Oander\Queue\Api\Data\JobSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Job
     * @param \Oander\Queue\Api\Data\JobInterface $job
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Oander\Queue\Api\Data\JobInterface $job
    );

    /**
     * Delete Job by ID
     * @param string $jobId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($jobId);
}
