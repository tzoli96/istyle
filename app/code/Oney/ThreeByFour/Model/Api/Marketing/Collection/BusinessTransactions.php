<?php

namespace Oney\ThreeByFour\Model\Api\Marketing\Collection;

use Magento\Framework\Api\Filter;

class BusinessTransactions extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var \Oney\ThreeByFour\Model\Api\Marketing\BusinessTransactions
     */
    protected $_businessTransactions;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Oney\ThreeByFour\Model\Api\Marketing\BusinessTransactions $businessTransactions,
        array $meta = [],
        array $data = []
    )
    {
        $this->_businessTransactions = $businessTransactions;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get Data
     *
     * @return array
     */
    public
    function getData()
    {
        return $this->_businessTransactions->getBusinessTransactions();
    }

    public function addFilter(Filter $filter)
    {
        return;
    }
}
