<?php
/**
 * Customer Address Validate
 * Copyright (C) 2019
 *
 * This file is part of Oander/CustomerAddressValidation.
 *
 * Oander/CustomerAddressValidation is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Oander\CustomerAddressValidation\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\InstallSchemaInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $table_oander_customeraddressvalidation_cityzip = $setup->getConnection()->newTable($setup->getTable(\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::TABLE));

        $table_oander_customeraddressvalidation_cityzip->addColumn(
            \Oander\CustomerAddressValidation\Api\Data\CityzipInterface::COUNTRYCODE,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => False],
            'Country Code'
        );

        $table_oander_customeraddressvalidation_cityzip->addColumn(
            \Oander\CustomerAddressValidation\Api\Data\CityzipInterface::ZIPCODE,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => False],
            'Zip Code'
        );

        $table_oander_customeraddressvalidation_cityzip->addColumn(
            \Oander\CustomerAddressValidation\Api\Data\CityzipInterface::CITY,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => False],
            'City'
        );

        $table_oander_customeraddressvalidation_cityzip->addIndex(
            $setup->getIdxName($setup->getTable(\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::TABLE),
                [
                    \Oander\CustomerAddressValidation\Api\Data\CityzipInterface::COUNTRYCODE,
                    \Oander\CustomerAddressValidation\Api\Data\CityzipInterface::ZIPCODE
                ]
            ),
            [
                \Oander\CustomerAddressValidation\Api\Data\CityzipInterface::COUNTRYCODE,
                \Oander\CustomerAddressValidation\Api\Data\CityzipInterface::ZIPCODE
            ]
        );

        $setup->getConnection()->createTable($table_oander_customeraddressvalidation_cityzip);
    }
}