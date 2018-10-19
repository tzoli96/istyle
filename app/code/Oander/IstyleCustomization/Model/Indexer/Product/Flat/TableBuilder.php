<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Model\Indexer\Product\Flat;

use Magento\Catalog\Model\Indexer\Product\Flat\Table\BuilderInterfaceFactory;

/**
 * Class TableBuilder
 * @package Oander\IstyleCustomization\Model\Indexer\Product\Flat
 */
class TableBuilder extends \Magento\Catalog\Model\Indexer\Product\Flat\TableBuilder
{

    /**
     * @var BuilderInterfaceFactory
     */
    private $tableBuilderFactory;

    /**
     * Create empty temporary table with given columns list
     *
     * @param string $tableName  Table name
     * @param array $columns array('columnName' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute, ...)
     * @param string $valueFieldSuffix
     *
     * @return array
     */
    protected function _createTemporaryTable($tableName, array $columns, $valueFieldSuffix)
    {
        $valueTables = [];
        if (!empty($columns)) {

            $valueTableName = $tableName . $valueFieldSuffix;
            $temporaryTableBuilder = $this->getTableBuilderFactory()->create(
                [
                    'connection' => $this->_connection,
                    'tableName' => $tableName
                ]
            );
            $valueTemporaryTableBuilder = $this->getTableBuilderFactory()->create(
                [
                    'connection' => $this->_connection,
                    'tableName' => $valueTableName
                ]
            );
            $flatColumns = $this->_productIndexerHelper->getFlatColumns();

            $temporaryTableBuilder->addColumn('entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER);

            $temporaryTableBuilder->addColumn('type_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT);

            $temporaryTableBuilder->addColumn('attribute_set_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER);

            $valueTemporaryTableBuilder->addColumn('entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER);

            /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            foreach ($columns as $columnName => $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                if (isset($flatColumns[$attributeCode])) {
                    $column = $flatColumns[$attributeCode];
                } else {
                    $column = $attribute->_getFlatColumnsDdlDefinition();
                    $column = $column[$attributeCode];
                }

                $temporaryTableBuilder->addColumn(
                    $columnName,
                    $column['type'],
                    isset($column['length']) ? $column['length'] : null
                );

                $columnValueName = $attributeCode . $valueFieldSuffix;
                if (isset($flatColumns[$columnValueName])) {
                    $columnValue = $flatColumns[$columnValueName];
                    $valueTemporaryTableBuilder->addColumn(
                        $columnValueName,
                        $columnValue['type'],
                        isset($columnValue['length']) ? $columnValue['length'] : null
                    );
                }
            }
            $this->_connection->dropTemporaryTable($tableName);

            $temporaryTableBuilder->getTable()->setOption('row_format','DYNAMIC');

            $this->_connection->createTemporaryTable($temporaryTableBuilder->getTable());

            if (count($valueTemporaryTableBuilder->getTable()->getColumns()) > 1) {
                $this->_connection->dropTemporaryTable($valueTableName);
                $this->_connection->createTemporaryTable($valueTemporaryTableBuilder->getTable());
                $valueTables[$valueTableName] = $valueTableName;
            }
        }
        return $valueTables;
    }

    /**
     * @return BuilderInterfaceFactory
     */
    private function getTableBuilderFactory()
    {
        if (null === $this->tableBuilderFactory) {
            $this->tableBuilderFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(BuilderInterfaceFactory::class);
        }

        return $this->tableBuilderFactory;
    }
}
