<?php
namespace Aheadworks\Popup\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Aheadworks\Popup\Setup\Updater\Schema\Updater as SchemaUpdater;

/**
 * Class UpgradeSchema
 * @package Aheadworks\Popup\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var SchemaUpdater
     */
    private $schemaUpdater;

    /**
     * @param SchemaUpdater $schemaUpdater
     */
    public function __construct(
        SchemaUpdater $schemaUpdater
    ) {
        $this->schemaUpdater = $schemaUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->schemaUpdater->update120($setup);
        }

        $setup->endSetup();
    }
}
