<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Model\Indexer\Product\Flat;

/**
 * Class FlatTableBuilder
 * @package Oander\IstyleCustomization\Model\Indexer\Product\Flat
 */
class FlatTableBuilder  extends \Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder
{
    /**
     * Prepare flat table for store
     *
     * @param int|string $storeId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _createTemporaryFlatTable($storeId)
    {
        $columns = $this->_productIndexerHelper->getFlatColumns();

        $indexesNeed = $this->_productIndexerHelper->getFlatIndexes();

        $maxIndex = $this->_config->getValue(
            self::XML_NODE_MAX_INDEX_COUNT
        );
        if ($maxIndex && count($indexesNeed) > $maxIndex) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'The Flat Catalog module has a limit of %2$d filterable and/or sortable attributes.'
                    . 'Currently there are %1$d of them.'
                    . 'Please reduce the number of filterable/sortable attributes in order to use this module',
                    count($indexesNeed),
                    $maxIndex
                )
            );
        }

        $indexKeys = [];
        $indexProps = array_values($indexesNeed);
        $upperPrimaryKey = strtoupper(\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_PRIMARY);
        foreach ($indexProps as $i => $indexProp) {
            $indexName = $this->_connection->getIndexName(
                $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($storeId)),
                $indexProp['fields'],
                $indexProp['type']
            );
            $indexProp['type'] = strtoupper($indexProp['type']);
            if ($indexProp['type'] == $upperPrimaryKey) {
                $indexKey = $upperPrimaryKey;
            } else {
                $indexKey = $indexName;
            }

            $indexProps[$i] = [
                'KEY_NAME' => $indexName,
                'COLUMNS_LIST' => $indexProp['fields'],
                'INDEX_TYPE' => strtolower($indexProp['type']),
            ];
            $indexKeys[$i] = $indexKey;
        }
        $indexesNeed = array_combine($indexKeys, $indexProps);

        /** @var $table \Magento\Framework\DB\Ddl\Table */
        $table = $this->_connection->newTable(
            $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($storeId))
        );
        foreach ($columns as $fieldName => $fieldProp) {
            $columnLength = isset($fieldProp['length']) ? $fieldProp['length'] : null;

            $columnDefinition = [
                'nullable' => isset($fieldProp['nullable']) ? (bool)$fieldProp['nullable'] : false,
                'unsigned' => isset($fieldProp['unsigned']) ? (bool)$fieldProp['unsigned'] : false,
                'default' => isset($fieldProp['default']) ? $fieldProp['default'] : false,
                'primary' => false,
            ];

            $columnComment = isset($fieldProp['comment']) ? $fieldProp['comment'] : $fieldName;

            $table->addColumn($fieldName, $fieldProp['type'], $columnLength, $columnDefinition, $columnComment);
        }

        foreach ($indexesNeed as $indexProp) {
            $table->addIndex(
                $indexProp['KEY_NAME'],
                $indexProp['COLUMNS_LIST'],
                ['type' => $indexProp['INDEX_TYPE']]
            );
        }

        $table->setComment("Catalog Product Flat (Store {$storeId})");
        $table->setOption('row_format','DYNAMIC');
        $this->_connection->dropTable(
            $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($storeId))
        );
        $this->_connection->createTable($table);
    }
}
