<?php

namespace Oander\ExternalRoundingUnit\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Oander\ExternalRoundingUnit\Enum\Attribute;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;
    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    )
    {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0') < 0) {
            $this->addAttributes($setup);
        }

        $setup->endSetup();
    }

    private function addAttributes($setup)
    {
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        $quoteSetup->addAttribute('quote', Attribute::EXTERNAL_ROUNDING_UNITE_QUOTE_ATTRIBUTE,
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '12,4',
                'visible' => false,
                'required' => false,
                'grid' => false
            ]
        );

        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $salesSetup->addAttribute('order', Attribute::EXTERNAL_ROUNDING_UNITE_ORDER_ATTRIBUTE,
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '12,4',
                'visible' => false,
                'required' => false,
                'grid' => false
            ]
        );
    }
}