<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oander\IstyleCustomization\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * InstallData constructor
     *
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if ($context->getVersion()
            && version_compare($context->getVersion(), '1.0.1') < 0
        ) {
            $this->addRegistrationNumAttribute($setup);
        }

        $setup->endSetup();
    }

    /**
     * Add date of birth attribute on customer address
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function addRegistrationNumAttribute(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        $attributeCode = 'pfpj_reg_no';
        $attributeParams = [
            'type' => 'varchar',
            'label' => 'Registration Number',
            'input' => 'text',
            'required' => false,
            'visible' => false,
            'user_defined' => false,
            'system' => false,
            'sort_order' => 60,
            'position' => 60,
        ];
        $eavSetup->addAttribute(
            'customer_address',
            $attributeCode,
            $attributeParams
        );
        $quoteSetup->addAttribute('quote_address', $attributeCode, $attributeParams);
        $salesSetup->addAttribute('order_address', $attributeCode, $attributeParams);

        /*$customerAddress = $eavSetup->getEntityTypeId('customer_address');
        $attributeIds = [];
        $select = $eavSetup->getSetup()->getConnection()->select()->from(
            ['ea' => $eavSetup->getSetup()->getTable('eav_attribute')],
            ['entity_type_id', 'attribute_code', 'attribute_id']
        )->where(
            'ea.entity_type_id IN(?)',
            [$customerAddress]
        );
        foreach ($eavSetup->getSetup()->getConnection()->fetchAll($select) as $row) {
            $attributeIds[$row['entity_type_id']][$row['attribute_code']] = $row['attribute_id'];
        }
        $attributeId = $attributeIds[$customerAddress][$attributeCode];
        $data = [
            ['form_code' => 'adminhtml_customer_address', 'attribute_id' => $attributeId],
            ['form_code' => 'customer_address_edit', 'attribute_id' => $attributeId],
            ['form_code' => 'customer_register_address', 'attribute_id' => $attributeId],
        ];

        $eavSetup
            ->getSetup()
            ->getConnection()
            ->insertMultiple($eavSetup->getSetup()->getTable('customer_form_attribute'), $data)
        ;*/
    }
}
