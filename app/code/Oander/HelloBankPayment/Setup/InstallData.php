<?php
namespace Oander\HelloBankPayment\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Oander\HelloBankPayment\Enum\Attribute;
use Oander\HelloBankPayment\Model\Config\Product\BaremOptions;

class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            Attribute::PRODUCT_BAREM_CODE,
            [
                'group' => 'HelloBank',
                'label' => 'Hello Bank Barems',
                'type'  => 'text',
                'input' => 'multiselect',
                'source' => BaremOptions::class,
                'required' => false,
                'sort_order' => 30,
                'global' => EavAttribute::SCOPE_STORE,
                'used_in_product_listing' => true,
                'backend' => ArrayBackend::class,
                'visible_on_front' => false
            ]
        );
    }
}