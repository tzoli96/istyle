<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace Oander\Autorelated\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Aheadworks\Autorelated\Model\Config;
use Aheadworks\Autorelated\Setup\Updater\Schema\Updater;

/**
 * Class InstallSchema
 *
 * @package Oander\Autorelated\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Updater
     */
    private $updater;

    /**
     * @param Updater $updater
     */
    public function __construct(
        Updater $updater
    ) {
        $this->updater = $updater;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'aw_autorelated_rule'
         */
        $installer->getConnection()->addColumn(
            $setup->getTable('aw_autorelated_rule'),
            'subtitle',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Rule Subtitle',
                'after' => 'title'
            ]
        );

        $installer->endSetup();
    }
}
