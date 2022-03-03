<?php

namespace Oander\AddressListAPI\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $table_oander_addresslist = $setup->getConnection()->newTable($setup->getTable(\Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::TABLE));

        $table_oander_addresslist->addColumn(
            \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::COUNTRY_CODE,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            4,
            ['nullable' => False],
            'Country Code'
        );

        $table_oander_addresslist->addColumn(
            \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::REGION,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => False],
            'region'
        );

        $table_oander_addresslist->addColumn(
            \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::CITY,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => False],
            'city'
        );

        $table_oander_addresslist->addIndex(
            $setup->getIdxName(
                \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::TABLE,
                [
                    \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::COUNTRY_CODE,
                    \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::REGION
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ),
            [
                \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::COUNTRY_CODE,
                \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::REGION
            ],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
        );


        $table_oander_addresslist->addIndex(
            $setup->getIdxName(
                \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::TABLE,
                [
                    \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::COUNTRY_CODE,
                    \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::REGION,
                    \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::CITY
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [
                \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::COUNTRY_CODE,
                \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::REGION,
                \Oander\AddressListAPI\Api\Data\OanderAddresslistInterface::CITY
            ],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        );

        $setup->getConnection()->createTable($table_oander_addresslist);
    }
}