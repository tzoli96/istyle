<?php


namespace Oander\Queue\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Oander\Queue\Api\Data\JobInterface;
use Oander\Queue\Api\Data\LogInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $table_oander_queue_job = $setup->getConnection()->newTable($setup->getTable(JobInterface::TABLE));

        $table_oander_queue_job->addColumn(
            JobInterface::JOB_ID,
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
            'Entity ID'
        );

        $table_oander_queue_job->addColumn(
            JobInterface::CLASS,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            512,
            ['nullable' => false],
            'Name of the Class'
        );

        $table_oander_queue_job->addColumn(
            JobInterface::DATA,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Data of class'
        );

        $table_oander_queue_job->addColumn(
            JobInterface::NAME,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            512,
            ['nullable' => false],
            'Name of job'
        );

        $table_oander_queue_job->addColumn(
            JobInterface::RETRIES,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'retries'
        );

        $table_oander_queue_job->addColumn(
            JobInterface::STATUS,
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['default' => '0','nullable' => false,'unsigned' => true],
            'Status of job'
        );

        $table_oander_queue_job->addColumn(
            JobInterface::CREATED_AT,
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'created_at'
        );

        $table_oander_queue_job->addIndex(
            $setup->getIdxName(
                $setup->getTable(JobInterface::TABLE),
                ['name'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['name'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        );

        $table_oander_queue_log = $setup->getConnection()->newTable($setup->getTable(LogInterface::TABLE));

        $table_oander_queue_log->addColumn(
            LogInterface::LOG_ID,
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
            'Entity ID'
        );

        $table_oander_queue_log->addColumn(
            LogInterface::JOB_ID,
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false,'unsigned' => true],
            'Job ID'
        );

        $table_oander_queue_log->addColumn(
            LogInterface::INPUT,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Input'
        );

        $table_oander_queue_log->addColumn(
            LogInterface::OUTPUT,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Output'
        );

        $table_oander_queue_log->addColumn(
            LogInterface::CREATED_AT,
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'created_at'
        );

        $table_oander_queue_log->addForeignKey(
            $setup->getFkName(
                LogInterface::TABLE,
                LogInterface::JOB_ID,
                JobInterface::TABLE,
                JobInterface::JOB_ID
            ),
            LogInterface::JOB_ID,
            $setup->getTable(JobInterface::TABLE),
            JobInterface::JOB_ID,
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table_oander_queue_job);

        $setup->getConnection()->createTable($table_oander_queue_log);
    }
}
