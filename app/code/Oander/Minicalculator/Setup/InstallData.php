<?php

namespace Oander\Minicalculator\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Oander\Minicalculator\Api\Data\CalculatorInterface;
use Oander\Minicalculator\Model\Config\Product\CalculatorType;
use Oander\Minicalculator\Model\Config\Product\CalculatorBarems;
use Oander\Minicalculator\Model\Config\Product\CalculatorInstallment;

class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ){
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            CalculatorInterface::CALCULATOR_TYPE,
            [
                'group' => 'minicalculator',
                'label' => 'Calculator type',
                'type'  => 'text',
                'input' => 'select',
                'source' => CalculatorType::class,
                'required' => false,
                'sort_order' => 30,
                'global' => EavAttribute::SCOPE_STORE,
                'used_in_product_listing' => true,
                'visible_on_front' => false
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            CalculatorInterface::CALCULATOR_BAREM,
            [
                'group' => 'minicalculator',
                'label' => 'Calculator Barem',
                'type'  => 'text',
                'input' => 'select',
                'source' => CalculatorBarems::class,
                'required' => false,
                'sort_order' => 40,
                'global' => EavAttribute::SCOPE_STORE,
                'used_in_product_listing' => true,
                'visible_on_front' => false
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            CalculatorInterface::CALCULATOR_INSTALLMENT,
            [
                'group' => 'minicalculator',
                'label' => 'Calculator installment',
                'type'  => 'text',
                'input' => 'select',
                'source' => CalculatorInstallment::class,
                'required' => false,
                'sort_order' => 50,
                'global' => EavAttribute::SCOPE_STORE,
                'used_in_product_listing' => true,
                'visible_on_front' => false
            ]
        );
    }
}