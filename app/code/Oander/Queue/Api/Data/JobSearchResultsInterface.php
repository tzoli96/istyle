<?php


namespace Oander\Queue\Api\Data;

interface JobSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Job list.
     * @return \Oander\Queue\Api\Data\JobInterface[]
     */
    public function getItems();

    /**
     * Set class list.
     * @param \Oander\Queue\Api\Data\JobInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
