<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\Autorelated\Rewrite\Aheadworks\Autorelated\Model\Wvtav\ResourceModel\Indexer;

class Wvtav extends \Aheadworks\Autorelated\Model\Wvtav\ResourceModel\Indexer\Wvtav
{
    /**
     * Collect sql and execute query for index the data
     *
     * @return \Aheadworks\Autorelated\Model\Wvtav\ResourceModel\Indexer\Wvtav
     */
    protected function prepareWvtavIndex()
    {
        $this->prepareWvtavIndexTable();

        $connection = $this->getConnection();
        $selectCustomer = clone $connection->select();
        $selectCustomer->from(
            ['v1' => $this->getReportViewProductViewIndexTable()],
            []
        )->joinInner(
            ['v2' => $this->getReportViewProductViewIndexTable()],
            'v1.customer_id=v2.customer_id',
            []
        )->where('v1.product_id <> v2.product_id');

        $productsPairSql = new \Zend_Db_Expr('CONCAT(v1.product_id, v2.product_id)');
        $selectCustomer->columns(
            [
                'master_product_id' => 'v1.product_id',
                'slave_product_id' => 'v2.product_id',
                'rating' => new \Zend_Db_Expr('COUNT(' . $productsPairSql . ')'),
            ]
        );

        $selectCustomer->group($productsPairSql);
        $this->addSessionPeriod($selectCustomer, $this->getIndexPeriod());

        $selectVisitor = clone $connection->select();
        $selectVisitor->from(
            ['v1' => $this->getReportViewProductViewIndexTable()],
            []
        )->joinInner(
            ['v2' => $this->getReportViewProductViewIndexTable()],
            'v1.visitor_id=v2.visitor_id',
            []
        )->where('v1.product_id <> v2.product_id');

        $selectVisitor->columns(
            [
                'master_product_id' => 'v1.product_id',
                'slave_product_id' => 'v2.product_id',
                'rating' => new \Zend_Db_Expr('COUNT(' . $productsPairSql . ')'),
            ]
        );

        $selectVisitor->group($productsPairSql);
        $this->addSessionPeriod($selectVisitor, $this->getIndexPeriod());


        $select = $connection->select();
        $select->reset();
        $select->union([$selectCustomer, $selectVisitor]);

        $query = $select->insertFromSelect(
            $this->getWvtavIndexTable(),
            ['master_product_id', 'slave_product_id', 'rating']
        );

        $connection->query($query);
        return $this;
    }
}