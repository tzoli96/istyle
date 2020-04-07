<?php

namespace Oander\IstyleCustomization\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Class Uninstall
 * @package Istyle_InsiaCare
 */
class Uninstall implements UninstallInterface
{
    /**
     * Eav setup factory
     *
     * @var $eavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Uninstall constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this->removeAttributes($setup);

        $installer->endSetup();
    }

    /**
     * Uninstall insurance attributes
     *
     * @param SchemaSetupInterface $setup
     */
    protected function removeAttributes(SchemaSetupInterface $setup)
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attributes = [
            'pfpj_reg_no',
        ];
        foreach ($attributes as $attribute) {
            $eavSetup->removeAttribute('order', $attribute);
        }
    }
}
