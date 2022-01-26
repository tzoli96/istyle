<?php


namespace Oander\Queue\Api\Data;

interface LogSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Log list.
     * @return \Oander\Queue\Api\Data\LogInterface[]
     */
    public function getItems();

    /**
     * Set job_id list.
     * @param \Oander\Queue\Api\Data\LogInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
