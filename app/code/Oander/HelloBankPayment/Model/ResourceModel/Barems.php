<?php
namespace Oander\HelloBankPayment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Barems extends AbstractDb
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(BaremInterface::TABLE_NAME, BaremInterface::ID);
    }
}
