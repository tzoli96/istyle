<?php


namespace Oander\Queue\Model\ResourceModel;

class Job extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('oander_queue_job', 'job_id');
    }
}
