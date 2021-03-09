<?php
namespace Oander\HelloBankPayment\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

use Magento\Framework\Setup\ModuleContextInterface;
use Oander\HelloBankPayment\Enum\Attribute;

class UpgradeData implements UpgradeDataInterface
{


    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0') < 0) {
            $this->addHelloBankStatusAttribute($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param $setup
     * @return void
     */
    private function addHelloBankStatusAttribute($setup)
    {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('sales_order'),
                Attribute::ORDER_HELLO_BANK_STATUS,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Custom Attribute'
                ]
            );

    }

}