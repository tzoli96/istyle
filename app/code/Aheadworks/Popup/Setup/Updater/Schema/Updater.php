<?php
namespace Aheadworks\Popup\Setup\Updater\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class Updater
 * @package Aheadworks\Popup\Setup\Updater\Schema
 */
class Updater
{
    /**
     * Update for 1.2.0 version
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     * @throws \Zend_Db_Exception
     */
    public function update120(SchemaSetupInterface $setup)
    {
        $this->createSegmentsTableTable($setup);
        
        return $this;
    }

    /**
     * Create segments table table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     * @throws \Zend_Db_Exception
     */
    private function createSegmentsTableTable($setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('aw_popup_block_segment'))
            ->addColumn(
                'popup_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Popup ID'
            )->addColumn(
                'segment_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Segment ID'
            )->addIndex(
                $setup->getIdxName('aw_popup_block_segment', ['popup_id', 'segment_id']),
                ['popup_id', 'segment_id']
            )->addForeignKey(
                $setup->getFkName(
                    'aw_popup_block_segment',
                    'popup_id',
                    'aw_popup_block',
                    'id'
                ),
                'popup_id',
                $setup->getTable('aw_popup_block'),
                'id',
                Table::ACTION_CASCADE
            )->setComment('AW Popup Segment Table');
        $setup->getConnection()->createTable($table);

        return $this;
    }
}
