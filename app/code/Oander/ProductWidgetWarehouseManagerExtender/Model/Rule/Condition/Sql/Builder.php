<?php

namespace Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\Sql;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Rule\Model\Condition\Combine;

/**
 * Class Builder
 * @package Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition
 */
class Builder extends \Magento\Rule\Model\Condition\Sql\Builder
{

    /**
     * Attach conditions filter to collection
     *
     * @param AbstractCollection $collection
     * @param Combine $combine
     *
     * @return void
     */
    public function attachConditionToCollection(
        AbstractCollection $collection,
        Combine $combine
    ) {
        $this->_connection = $collection->getResource('warehouse')->getConnection();
        $this->_joinTablesToCollection($collection, $combine);
        $whereExpression = (string)$this->_getMappedSqlCombination($combine);
        if (!empty($whereExpression)) {
            // Select ::where method adds braces even on empty expression
            $collection->getSelect()->where($whereExpression);
        }
    }

    /**
     * Attach conditions filter to collection
     *
     * @param AbstractCollection $collection
     * @param Combine $combine
     *
     * @return string
     */
    public function getWhereConditionToCollection(
        AbstractCollection $collection,
        Combine $combine
    ) {
        $this->_connection = $collection->getResource('warehouse')->getConnection();
        $this->_joinTablesToCollection($collection, $combine);
        $where = (string)$this->_getMappedSqlCombination($combine);
        if (!empty($where)) {
            $where = 'WHERE '.$where;
        }

        return $where;
    }
}
