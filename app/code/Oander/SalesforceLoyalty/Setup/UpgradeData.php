<?php
/**
 * Salesforce Loyalty module
 * Copyright (C) 2019
 *
 * This file is part of Oander/SalesforceLoyalty.
 *
 * Oander/SalesforceLoyalty is free software: you can redistribute it and/or modify
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

namespace Oander\SalesforceLoyalty\Setup;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Oander\SalesforceLoyalty\Enum\Attribute;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;
use Magento\Cms\Model\BlockFactory;
use Magento\Store\Api\StoreRepositoryInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var BlockFactory
     */
    private $blockFactory;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;
    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;
    /**
     * @var Config
     */
    private $eavConfig;
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     * @param Config $eavConfig
     * @param AttributeSetFactory $attributeSetFactory
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        EavSetupFactory     $eavSetupFactory,
        QuoteSetupFactory   $quoteSetupFactory,
        SalesSetupFactory   $salesSetupFactory,
        Config              $eavConfig,
        AttributeSetFactory $attributeSetFactory,
        BlockFactory        $blockFactory,
        StoreRepositoryInterface $storeRepository
    )
    {
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->blockFactory = $blockFactory;
        $this->storeRepository = $storeRepository;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface   $context
    )
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), "1.0.1", "<")) {
            $this->addProductLoyaltyPercentAttribute($eavSetup);
        }
        if (version_compare($context->getVersion(), "1.0.2", "<")) {
            $this->addOrderLoyaltyAttribute($setup);
        }
        if (version_compare($context->getVersion(), "1.0.6", "<")) {
            $this->addCustomerAttribute($eavSetup);
        }
        if (version_compare($context->getVersion(), "1.0.9", "<")) {
            $this->addTemporaryPeriodBlock();
        }
        if (version_compare($context->getVersion(), "1.1.0", "<")) {
            $this->loyaltyPromoBlock();
        }
    }

    private function loyaltyPromoBlock()
    {
        $stores = $this->storeRepository->getList();
        $storeIds = [];

        foreach($stores as $store)
        {
            $this->blockFactory->create()->setData([
                'title' => 'Loyalty Promo Block '.$store->getCode(),
                'identifier' => 'loyalty_promo_block_'.$store->getCode(),
                'stores' => $storeIds,
                'is_active' => 1,
            ])->save();
        }
    }

    /**
     * @throws \Exception
     * @return void
     */
    private function addTemporaryPeriodBlock()
    {
        $stores = $this->storeRepository->getList();
        $storeIds = [];

        foreach($stores as $store)
        {
            $this->blockFactory->create()->setData([
                'title' => 'Temporary Period Loyalty Registration Block '.$store->getCode(),
                'identifier' => 'temporary_period_loyalty_registration_block_'.$store->getCode(),
                'stores' => $storeIds,
                'is_active' => 1,
            ])->save();
        }
    }

    /**
     * @param $eavSetup
     * @throws LocalizedException
     * @return void
     */
    private function addCustomerAttribute($eavSetup)
    {
        $attributes = [
            CustomerAttribute::REGISTRED_TO_LOYALTY,
            CustomerAttribute::REGISTER_TO_LOYALTY
        ];
        $customerEntity = $this->eavConfig->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        foreach ($attributes as $attribute) {
            $eavSetup->addAttribute(
                Customer::ENTITY,
                $attribute,
                [
                    'type' => 'int',
                    'label' => 'Register to loyalty',
                    'input' => 'select',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => true,
                    'system' => 0,
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
            $attributes = $this->eavConfig->getAttribute(Customer::ENTITY, $attribute);
            $attributes->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms', ['adminhtml_customer']
            ]);
            $attributes->save();
        }

    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function addOrderLoyaltyAttribute(ModuleDataSetupInterface $setup)
    {
        $attributes = [
            Attribute::LOYALTY_POINT,
            Attribute::LOYALTY_BLOCK_TRANSACTION_ID
        ];
        foreach ($attributes as $attribute) {
            $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
            $quoteSetup->addAttribute('quote', $attribute,
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => '11',
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
            $salesSetup->addAttribute('order', $attribute,
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => '11',
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );
        }
    }

    private function addProductLoyaltyPercentAttribute(\Magento\Eav\Setup\EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            \Oander\SalesforceLoyalty\Enum\ProductAttribute::LOYALTY_POINTS_PERCENT,
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Loyalty points percent',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => 0,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => 'simple,virtual',
                'system' => 1,
                'group' => 'General',
                'option' => ['values' => [""]]
            ]
        );
    }
}