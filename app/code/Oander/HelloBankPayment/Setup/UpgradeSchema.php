<?php
namespace Oander\HelloBankPayment\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Oander\HelloBankPayment\Api\Data\BaremInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.1.4') < 0) {
            $this->changeBaremIdType($setup);
        }
        if (version_compare($context->getVersion(), '1.1.3') < 0) {
           $this->addColumns($setup);
        }

        $setup->endSetup();
    }

    private function addColumns($setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(BaremInterface::TABLE_NAME),
            BaremInterface::EQUITY,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Equity'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function changeBaremIdType(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $installer->startSetup();
        if ($installer->getConnection()->tableColumnExists(BaremInterface::TABLE_NAME, BaremInterface::BAREM_ID)){
            $definition = [
                'type'      => Table::TYPE_TEXT,
                'length'    => 255
            ];
            $installer->getConnection()->modifyColumn(
                $setup->getTable(BaremInterface::TABLE_NAME),
                BaremInterface::BAREM_ID,
                $definition
            );
        }
    }
}