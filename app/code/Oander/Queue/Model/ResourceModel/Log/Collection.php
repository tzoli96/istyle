<?php


namespace Oander\Queue\Model\ResourceModel\Log;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Oander\Queue\Model\Log::class,
            \Oander\Queue\Model\ResourceModel\Log::class
        );
    }
}
