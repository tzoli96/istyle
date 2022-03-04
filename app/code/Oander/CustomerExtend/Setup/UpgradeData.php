<?php

declare(strict_types=1);

namespace Oander\CustomerExtend\Setup;

use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion()
            && version_compare($context->getVersion(), '1.0.1') < 0
        ) {
            $this->upgrade_1_0_1($setup);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function upgrade_1_0_1(ModuleDataSetupInterface $setup)
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerAddressEntity = $customerSetup->getEavConfig()->getEntityType('customer_address');
        $attributeSetId = $customerAddressEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute('customer_address', 'is_company', [
            'type'          => 'int',
            'label'         => 'IsCompany',
            'input'         => 'select',
            'source'        => 'Oander\CustomerExtend\Model\Entity\Attribute\Source\IsCompany',
            'required'      =>  true,
            'default'       =>  0,
            'visible'       =>  true,
            'sort_order'    =>  1,
            'position'      =>  1,
            'system'        =>  0,
            'note'          => 'Customer is Company or Not'
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'is_company');
        $attribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId
            ]);

        $attribute->setData('used_in_forms', [
            'adminhtml_customer',
            'adminhtml_checkout',
            'adminhtml_customer_address',
            'customer_account_edit',
            'customer_address_edit',
            'customer_register_address',
        ]);

        $attribute->save();

    }
}