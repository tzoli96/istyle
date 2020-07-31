<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Oander\FanCourierValidator\Api\Data\CityInterface;
use Oander\FanCourierValidator\Api\Data\StateCityInterface;
use Oander\FanCourierValidator\Api\Data\StateInterface;

/**
 * Class InstallSchema
 * @package Oander\FanCourierValidator\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createStateTable($setup);
        $this->createCityTable($setup);
        $this->createStateCityTable($setup);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return void
     * @throws \Zend_Db_Exception
     */
    protected function createStateTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = StateInterface::TABLE_NAME;

        $table = $connection->newTable($tableName)
            ->addColumn(
                StateInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'Entity Id'
            )->addColumn(
                StateInterface::STATE,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false,
                ],
                'State'
            );

        $connection->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return void
     * @throws \Zend_Db_Exception
     */
    protected function createCityTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = CityInterface::TABLE_NAME;

        $table = $connection->newTable($tableName)
            ->addColumn(
                CityInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'Entity Id'
            )->addColumn(
                CityInterface::CITY,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false,
                ],
                'City'
            );

        $connection->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return void
     * @throws \Zend_Db_Exception
     */
    protected function createStateCityTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = StateCityInterface::TABLE_NAME;
        $stateTable = $setup->getTable(StateInterface::TABLE_NAME);
        $cityTable = $setup->getTable(CityInterface::TABLE_NAME);

        $table = $connection->newTable($tableName)
            ->addColumn(
                StateCityInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'Entity Id'
            )->addColumn(
                StateCityInterface::STATE_ID,
                Table::TYPE_INTEGER,
                    null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'State ID'
            )->addColumn(
                StateCityInterface::CITY_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'City ID'
            )->addForeignKey(
                $setup->getFkName(
                    $tableName,
                    StateCityInterface::STATE_ID,
                    $stateTable,
                    StateInterface::ENTITY_ID
                ),
                StateCityInterface::STATE_ID,
                $stateTable,
                StateInterface::ENTITY_ID,
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $tableName,
                    StateCityInterface::CITY_ID,
                    $cityTable,
                    CityInterface::ENTITY_ID
                ),
                StateCityInterface::CITY_ID,
                $cityTable,
                CityInterface::ENTITY_ID,
                Table::ACTION_CASCADE
            );;

        $connection->createTable($table);
    }
}
