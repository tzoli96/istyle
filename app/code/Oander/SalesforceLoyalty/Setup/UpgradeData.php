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
use Oander\SalesforceLoyalty\Enum\CMSBlock as CMSBlockAlias;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
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
     * @var BlockCollectionFactory
     */
    private $blockCollectionFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     * @param Config $eavConfig
     * @param AttributeSetFactory $attributeSetFactory
     * @param BlockFactory $blockFactory
     * @param BlockCollectionFactory $blockCollectionFactory
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        EavSetupFactory     $eavSetupFactory,
        QuoteSetupFactory   $quoteSetupFactory,
        SalesSetupFactory   $salesSetupFactory,
        Config              $eavConfig,
        AttributeSetFactory $attributeSetFactory,
        BlockFactory        $blockFactory,
        BlockCollectionFactory $blockCollectionFactory,
        StoreRepositoryInterface $storeRepository
    )
    {
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->blockFactory = $blockFactory;
        $this->blockCollectionFactory = $blockCollectionFactory;
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
        //Removed based on 57566 ticket
        /*if (version_compare($context->getVersion(), "1.0.6", "<")) {
            $this->addCustomerAttribute($eavSetup);
        }*/
        /*if (version_compare($context->getVersion(), "1.0.9", "<")) {
            $this->addTemporaryPeriodBlock();
        }
        if (version_compare($context->getVersion(), "1.1.0", "<")) {
            $this->loyaltyPromoBlock();
        }*/
        //Removed based on 57566 ticket
        /*if (version_compare($context->getVersion(), "1.1.1", "<")) {
            $this->changeRegisteredToLoyaltyAttribute($eavSetup);
        }*/
        //Add based on 57566 ticket, remove attributes if already added
        if (version_compare($context->getVersion(), "1.1.2", "<") && version_compare($context->getVersion(), "1.0.5", ">")) {
            $this->removeCustomerAttribute($eavSetup, 'register_to_loyalty');
            $this->removeCustomerAttribute($eavSetup, 'registered_to_loyalty');
        }
        if(version_compare($context->getVersion(), "1.1.2", "<")) {
            $this->addLoyaltyStatusAttribute($eavSetup);
            $this->addCMSBlock(CMSBlockAlias::PROMO, 'Loyalty Promo Block');
            $this->addCMSBlock(CMSBlockAlias::REGISTERING, 'Loyalty Registering Block');
            $this->addCMSBlock(CMSBlockAlias::CONFIRMATION, 'Loyalty Email Confirmation Block');
            $this->addCMSBlock(CMSBlockAlias::PROFILE, 'Loyalty Registered Profile Block');
        }
        if(version_compare($context->getVersion(), "1.1.3", "<")) {
            $this->addCMSBlock('loyalty_nosfid', 'Loyalty Missing SalesForce ID Profile Block');
        }
        if (version_compare($context->getVersion(), "1.1.4", "<") && version_compare($context->getVersion(), "1.1.2", ">")) {
            $this->removeCMSBlock('loyalty_nosfid');
        }
        if (version_compare($context->getVersion(), "1.1.5", "<")) {
            $this->addQuoteAddressLoyaltyAttribute($setup);
        }
    }

    /**
     * @param $id
     * @param $title
     * @return void
     */
    private function addCMSBlock($id, $title)
    {
        $stores = $this->storeRepository->getList();

        foreach($stores as $store)
        {
            if($store->getId()!==0) {
                //Maybe block already exist
                try {
                    $this->blockFactory->create()->setData([
                        'title' => $title,
                        'identifier' => $id,
                        'stores' => [$store->getId()],
                        'is_active' => 1,
                    ])->save();
                } catch (\Exception $e) {}
            }
        }
    }

    /**
     * @param $id
     * @param $title
     * @return void
     */
    private function removeCMSBlock($id)
    {
        /** @var \Magento\Cms\Model\ResourceModel\Block\Collection $blockCollection */
        $blockCollection = $this->blockCollectionFactory->create();
        $blockCollection->addFieldToFilter(\Magento\Cms\Api\Data\BlockInterface::IDENTIFIER, $id);
        $blockCollection->walk('delete');
    }

    private function removeCustomerAttribute($eavSetup, $id) {
        $eavSetup->removeAttribute(Customer::ENTITY, $id);
    }

    /**
     * @param $eavSetup
     * @throws LocalizedException
     * @return void
     */
    private function addLoyaltyStatusAttribute($eavSetup)
    {
        $customerEntity = $this->eavConfig->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $eavSetup->addAttribute(
            Customer::ENTITY,
            CustomerAttribute::LOYALTY_STATUS,
            [
                'type' => 'int',
                'label' => 'Loyalty Status',
                'input' => 'select',
                'required' => false,
                'default' => \Oander\SalesforceLoyalty\Enum\LoyaltyStatus::VALUE_NOT_REGISTERED,
                'visible' => true,
                'user_defined' => false,
                'system' => 0,
                'source' => \Oander\SalesforceLoyalty\Model\Entity\Attribute\Source\LoyaltyStatus::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );
        $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, CustomerAttribute::LOYALTY_STATUS);
        $attribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer']
        ]);
        $attribute->save();
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

    private function addQuoteAddressLoyaltyAttribute(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->addColumn($setup->getTable('quote_address'),
            'loyalty_discount_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '0.0000',
                'comment' => 'Loyalty Amount'
            ]
        );
    }
}