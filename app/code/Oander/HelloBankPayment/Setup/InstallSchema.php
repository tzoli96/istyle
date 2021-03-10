<?php
namespace Oander\HelloBankPayment\Setup;

use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class InstallSchema implements InstallSchemaInterface
{

    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable(BaremInterface::TABLE_NAME)
        )->addColumn(
            BaremInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Barem Entity Id'
        )->addColumn(
            BaremInterface::BAREM_NAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Barem name'
        )->addColumn(
            BaremInterface::BAREM_ID,
            Table::TYPE_SMALLINT,
            null,
            [],
            'Barem Id'
        )->addColumn(
            BaremInterface::PRIORITY,
            Table::TYPE_SMALLINT,
            255,
            [],
            'Barem Priority'
        )->addColumn(
            BaremInterface::MAXIMUM_PRICE,
            Table::TYPE_TEXT,
            255,
            [],
            'Barem Maximum Price'
        )->addColumn(
            BaremInterface::MINIMUM_PRICE,
            Table::TYPE_TEXT,
            255,
            [],
            'Barem Minimum Price'
        )->addColumn(
            BaremInterface::INSTALLMENTS,
            Table::TYPE_TEXT,
            255,
            [],
            'Barem Installments'
        )->addColumn(
            BaremInterface::INSTALLMENTS_TYPE,
            Table::TYPE_SMALLINT,
            null,
            [],
            'Barem Insatllments Type'
        )->addColumn(
            BaremInterface::DEFAULT_INSTALLMENT,
            Table::TYPE_TEXT,
            255,
            [],
            'Barem Default installment'
        )->addColumn(
            BaremInterface::STATUS,
            Table::TYPE_SMALLINT,
            null,
            [],
            'Barem Status'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            [
                'nullable' => false,
                'default' => Table::TIMESTAMP_INIT,
            ],
            'Barem Creation Time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Barem Time'
        )->setComment(
            'HelloBank Barems'
        );

        try {
            $installer->getConnection()->createTable($table);
        } catch (Zend_Db_Exception $e) {
            die($e->getMessage());
        }
        $installer->endSetup();
    }
}