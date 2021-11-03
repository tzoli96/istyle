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
use Oander\IstyleCustomization\Enum\OrderAttributeEnum;
use Magento\Framework\DB\Ddl\Table;
use Magento\Sales\Model\Order;

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
     * @param EavSetupFactory   $eavSetupFactory
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

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $this->addItemGroupAndItemTypeAttributes($setup);
        }
      
        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $this->addProductDistributorAttribute($setup);
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $this->addNewOrderAttributeParibas($setup);
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $this->setAddressAttributesPosition($setup);
        }

        $setup->endSetup();
    }

    private function setAddressAttributesPosition(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->delete(
            $setup->getConnection()->getTableName("core_config_data"),
            "path = 'customer/address_attributes_order/address_attributes_positions'"
        );
        $setup->getConnection()->insertOnDuplicate(
            $setup->getConnection()->getTableName("core_config_data"),
            [
                "scope" =>"stores",
                "scope_id" => 3,
                "path" => "customer/address_attributes_order/address_attributes_positions",
                "value" => 'a:11:{s:17:"_1635208744016_16";a:4:{s:9:"attribute";s:8:"lastname";s:19:"individual_position";s:2:"20";s:16:"company_position";s:1:"8";s:16:"default_position";s:1:"2";}s:18:"_1635208751827_827";a:4:{s:9:"attribute";s:9:"firstname";s:19:"individual_position";s:2:"10";s:16:"company_position";s:1:"7";s:16:"default_position";s:1:"1";}s:18:"_1635209618570_570";a:4:{s:9:"attribute";s:8:"postcode";s:19:"individual_position";s:2:"30";s:16:"company_position";s:1:"3";s:16:"default_position";s:2:"10";}s:17:"_1635209655045_45";a:4:{s:9:"attribute";s:4:"city";s:19:"individual_position";s:2:"40";s:16:"company_position";s:1:"4";s:16:"default_position";s:1:"9";}s:18:"_1635209662708_708";a:4:{s:9:"attribute";s:6:"street";s:19:"individual_position";s:2:"50";s:16:"company_position";s:1:"5";s:16:"default_position";s:1:"6";}s:18:"_1635209677834_834";a:4:{s:9:"attribute";s:9:"telephone";s:19:"individual_position";s:2:"60";s:16:"company_position";s:1:"6";s:16:"default_position";s:1:"4";}s:18:"_1635209696389_389";a:4:{s:9:"attribute";s:7:"company";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"1";s:16:"default_position";s:1:"3";}s:18:"_1635209710156_156";a:4:{s:9:"attribute";s:6:"vat_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"2";s:16:"default_position";s:1:"7";}s:17:"_1635244690078_78";a:4:{s:9:"attribute";s:3:"fax";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"5";}s:18:"_1635244766958_958";a:4:{s:9:"attribute";s:6:"region";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"8";}s:18:"_1635245262683_683";a:4:{s:9:"attribute";s:10:"country_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:2:"11";}}'
            ]
        );

        $setup->getConnection()->insertOnDuplicate(
            $setup->getConnection()->getTableName("core_config_data"),
            [
                "scope" =>"stores",
                "scope_id" => 11,
                "path" => "customer/address_attributes_order/address_attributes_positions",
                "value" => 'a:11:{s:18:"_1635245993779_779";a:4:{s:9:"attribute";s:9:"firstname";s:19:"individual_position";s:2:"10";s:16:"company_position";s:1:"7";s:16:"default_position";s:1:"1";}s:18:"_1635246003689_689";a:4:{s:9:"attribute";s:8:"lastname";s:19:"individual_position";s:2:"20";s:16:"company_position";s:1:"8";s:16:"default_position";s:1:"2";}s:18:"_1635246009358_358";a:4:{s:9:"attribute";s:4:"city";s:19:"individual_position";s:2:"30";s:16:"company_position";s:1:"3";s:16:"default_position";s:1:"9";}s:18:"_1635246012131_131";a:4:{s:9:"attribute";s:8:"postcode";s:19:"individual_position";s:2:"40";s:16:"company_position";s:1:"4";s:16:"default_position";s:2:"10";}s:18:"_1635246018334_334";a:4:{s:9:"attribute";s:6:"street";s:19:"individual_position";s:2:"50";s:16:"company_position";s:1:"5";s:16:"default_position";s:1:"6";}s:18:"_1635246024907_907";a:4:{s:9:"attribute";s:9:"telephone";s:19:"individual_position";s:2:"60";s:16:"company_position";s:1:"6";s:16:"default_position";s:1:"4";}s:16:"_1635246167002_2";a:4:{s:9:"attribute";s:7:"company";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"1";s:16:"default_position";s:1:"3";}s:18:"_1635246171370_370";a:4:{s:9:"attribute";s:6:"vat_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"2";s:16:"default_position";s:1:"7";}s:18:"_1635246286103_103";a:4:{s:9:"attribute";s:3:"fax";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"5";}s:18:"_1635246309768_768";a:4:{s:9:"attribute";s:6:"region";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"8";}s:18:"_1635246337224_224";a:4:{s:9:"attribute";s:10:"country_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:2:"11";}}'
            ]
        );

        $setup->getConnection()->insertOnDuplicate(
            $setup->getConnection()->getTableName("core_config_data"),
            [
                "scope" =>"stores",
                "scope_id" => 2,
                "path" => "customer/address_attributes_order/address_attributes_positions",
                "value" => 'a:12:{s:18:"_1635246409537_537";a:4:{s:9:"attribute";s:9:"firstname";s:19:"individual_position";s:2:"10";s:16:"company_position";s:1:"8";s:16:"default_position";s:1:"1";}s:18:"_1635246411379_379";a:4:{s:9:"attribute";s:8:"lastname";s:19:"individual_position";s:2:"20";s:16:"company_position";s:1:"9";s:16:"default_position";s:1:"2";}s:18:"_1635246416166_166";a:4:{s:9:"attribute";s:6:"street";s:19:"individual_position";s:2:"30";s:16:"company_position";s:1:"3";s:16:"default_position";s:1:"6";}s:18:"_1635246421671_671";a:4:{s:9:"attribute";s:4:"city";s:19:"individual_position";s:2:"40";s:16:"company_position";s:1:"4";s:16:"default_position";s:1:"9";}s:17:"_1635246425062_62";a:4:{s:9:"attribute";s:8:"postcode";s:19:"individual_position";s:2:"50";s:16:"company_position";s:1:"5";s:16:"default_position";s:2:"10";}s:17:"_1635246436081_81";a:4:{s:9:"attribute";s:9:"telephone";s:19:"individual_position";s:2:"60";s:16:"company_position";s:1:"6";s:16:"default_position";s:1:"4";}s:18:"_1635246460888_888";a:4:{s:9:"attribute";s:7:"company";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"1";s:16:"default_position";s:1:"3";}s:17:"_1635246466088_88";a:4:{s:9:"attribute";s:11:"pfpj_reg_no";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"2";s:16:"default_position";s:0:"";}s:18:"_1635246488593_593";a:4:{s:9:"attribute";s:6:"vat_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"7";s:16:"default_position";s:1:"7";}s:18:"_1635246516264_264";a:4:{s:9:"attribute";s:3:"fax";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"5";}s:17:"_1635246539075_75";a:4:{s:9:"attribute";s:6:"region";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"8";}s:18:"_1635246558899_899";a:4:{s:9:"attribute";s:10:"country_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:2:"11";}}'
            ]
        );

        $setup->getConnection()->insertOnDuplicate(
            $setup->getConnection()->getTableName("core_config_data"),
            [
                "scope" =>"stores",
                "scope_id" => 10,
                "path" => "customer/address_attributes_order/address_attributes_positions",
                "value" => 'a:11:{s:18:"_1635246631822_822";a:4:{s:9:"attribute";s:9:"firstname";s:19:"individual_position";s:2:"10";s:16:"company_position";s:1:"7";s:16:"default_position";s:1:"1";}s:18:"_1635246634518_518";a:4:{s:9:"attribute";s:8:"lastname";s:19:"individual_position";s:2:"20";s:16:"company_position";s:1:"8";s:16:"default_position";s:1:"2";}s:18:"_1635246636709_709";a:4:{s:9:"attribute";s:4:"city";s:19:"individual_position";s:2:"30";s:16:"company_position";s:1:"3";s:16:"default_position";s:1:"9";}s:18:"_1635246639934_934";a:4:{s:9:"attribute";s:8:"postcode";s:19:"individual_position";s:2:"40";s:16:"company_position";s:1:"4";s:16:"default_position";s:2:"10";}s:18:"_1635246643960_960";a:4:{s:9:"attribute";s:6:"street";s:19:"individual_position";s:2:"50";s:16:"company_position";s:1:"5";s:16:"default_position";s:1:"6";}s:18:"_1635246647425_425";a:4:{s:9:"attribute";s:9:"telephone";s:19:"individual_position";s:2:"60";s:16:"company_position";s:1:"6";s:16:"default_position";s:1:"4";}s:18:"_1635246675727_727";a:4:{s:9:"attribute";s:7:"company";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"1";s:16:"default_position";s:1:"3";}s:18:"_1635246678897_897";a:4:{s:9:"attribute";s:6:"vat_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"2";s:16:"default_position";s:1:"7";}s:18:"_1635246729219_219";a:4:{s:9:"attribute";s:3:"fax";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"5";}s:18:"_1635246744342_342";a:4:{s:9:"attribute";s:6:"region";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"8";}s:18:"_1635246762266_266";a:4:{s:9:"attribute";s:10:"country_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:2:"11";}}'
            ]
        );

        $setup->getConnection()->insertOnDuplicate(
            $setup->getConnection()->getTableName("core_config_data"),
            [
                "scope" =>"stores",
                "scope_id" => 9,
                "path" => "customer/address_attributes_order/address_attributes_positions",
                "value" => 'a:12:{s:18:"_1635246817586_586";a:4:{s:9:"attribute";s:9:"firstname";s:19:"individual_position";s:2:"10";s:16:"company_position";s:1:"8";s:16:"default_position";s:1:"1";}s:18:"_1635246820655_655";a:4:{s:9:"attribute";s:8:"lastname";s:19:"individual_position";s:2:"20";s:16:"company_position";s:1:"9";s:16:"default_position";s:1:"2";}s:18:"_1635246823738_738";a:4:{s:9:"attribute";s:8:"postcode";s:19:"individual_position";s:2:"30";s:16:"company_position";s:1:"4";s:16:"default_position";s:2:"10";}s:18:"_1635246833901_901";a:4:{s:9:"attribute";s:4:"city";s:19:"individual_position";s:2:"40";s:16:"company_position";s:1:"5";s:16:"default_position";s:1:"9";}s:18:"_1635246837230_230";a:4:{s:9:"attribute";s:6:"street";s:19:"individual_position";s:2:"50";s:16:"company_position";s:1:"6";s:16:"default_position";s:1:"6";}s:18:"_1635246852315_315";a:4:{s:9:"attribute";s:9:"telephone";s:19:"individual_position";s:2:"60";s:16:"company_position";s:1:"7";s:16:"default_position";s:1:"4";}s:18:"_1635246864599_599";a:4:{s:9:"attribute";s:7:"company";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"1";s:16:"default_position";s:1:"3";}s:18:"_1635246868643_643";a:4:{s:9:"attribute";s:11:"pfpj_reg_no";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"2";s:16:"default_position";s:0:"";}s:18:"_1635246903434_434";a:4:{s:9:"attribute";s:6:"vat_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"3";s:16:"default_position";s:1:"7";}s:18:"_1635246985533_533";a:4:{s:9:"attribute";s:3:"fax";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"5";}s:18:"_1635247007146_146";a:4:{s:9:"attribute";s:6:"region";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"8";}s:18:"_1635247022482_482";a:4:{s:9:"attribute";s:10:"country_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:2:"11";}}'
            ]
        );

        $setup->getConnection()->insertOnDuplicate(
            $setup->getConnection()->getTableName("core_config_data"),
            [
                "scope" =>"stores",
                "scope_id" => 6,
                "path" => "customer/address_attributes_order/address_attributes_positions",
                "value" => 'a:11:{s:18:"_1635247085370_370";a:4:{s:9:"attribute";s:9:"firstname";s:19:"individual_position";s:2:"10";s:16:"company_position";s:1:"6";s:16:"default_position";s:1:"1";}s:17:"_1635247089089_89";a:4:{s:9:"attribute";s:8:"lastname";s:19:"individual_position";s:2:"20";s:16:"company_position";s:1:"7";s:16:"default_position";s:1:"2";}s:18:"_1635247091925_925";a:4:{s:9:"attribute";s:6:"street";s:19:"individual_position";s:2:"30";s:16:"company_position";s:1:"3";s:16:"default_position";s:1:"6";}s:18:"_1635247111286_286";a:4:{s:9:"attribute";s:8:"postcode";s:19:"individual_position";s:2:"40";s:16:"company_position";s:1:"4";s:16:"default_position";s:2:"10";}s:18:"_1635247115738_738";a:4:{s:9:"attribute";s:4:"city";s:19:"individual_position";s:2:"50";s:16:"company_position";s:1:"5";s:16:"default_position";s:1:"9";}s:18:"_1635247130944_944";a:4:{s:9:"attribute";s:9:"telephone";s:19:"individual_position";s:2:"60";s:16:"company_position";s:1:"8";s:16:"default_position";s:1:"4";}s:17:"_1635247138074_74";a:4:{s:9:"attribute";s:7:"company";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"1";s:16:"default_position";s:1:"3";}s:18:"_1635247141847_847";a:4:{s:9:"attribute";s:6:"vat_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"2";s:16:"default_position";s:1:"7";}s:18:"_1635247166417_417";a:4:{s:9:"attribute";s:3:"fax";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"5";}s:18:"_1635247206885_885";a:4:{s:9:"attribute";s:6:"region";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"8";}s:18:"_1635247220735_735";a:4:{s:9:"attribute";s:10:"country_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:2:"11";}}'
            ]
        );

        $setup->getConnection()->insertOnDuplicate(
            $setup->getConnection()->getTableName("core_config_data"),
            [
                "scope" =>"stores",
                "scope_id" => 8,
                "path" => "customer/address_attributes_order/address_attributes_positions",
                "value" => 'a:11:{s:18:"_1635247085370_370";a:4:{s:9:"attribute";s:9:"firstname";s:19:"individual_position";s:2:"10";s:16:"company_position";s:1:"6";s:16:"default_position";s:1:"1";}s:17:"_1635247089089_89";a:4:{s:9:"attribute";s:8:"lastname";s:19:"individual_position";s:2:"20";s:16:"company_position";s:1:"7";s:16:"default_position";s:1:"2";}s:18:"_1635247091925_925";a:4:{s:9:"attribute";s:6:"street";s:19:"individual_position";s:2:"30";s:16:"company_position";s:1:"3";s:16:"default_position";s:1:"6";}s:18:"_1635247111286_286";a:4:{s:9:"attribute";s:8:"postcode";s:19:"individual_position";s:2:"40";s:16:"company_position";s:1:"4";s:16:"default_position";s:2:"10";}s:18:"_1635247115738_738";a:4:{s:9:"attribute";s:4:"city";s:19:"individual_position";s:2:"50";s:16:"company_position";s:1:"5";s:16:"default_position";s:1:"9";}s:18:"_1635247130944_944";a:4:{s:9:"attribute";s:9:"telephone";s:19:"individual_position";s:2:"60";s:16:"company_position";s:1:"8";s:16:"default_position";s:1:"4";}s:17:"_1635247138074_74";a:4:{s:9:"attribute";s:7:"company";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"1";s:16:"default_position";s:1:"3";}s:18:"_1635247141847_847";a:4:{s:9:"attribute";s:6:"vat_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"2";s:16:"default_position";s:1:"7";}s:18:"_1635247166417_417";a:4:{s:9:"attribute";s:3:"fax";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"5";}s:18:"_1635247206885_885";a:4:{s:9:"attribute";s:6:"region";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"8";}s:18:"_1635247220735_735";a:4:{s:9:"attribute";s:10:"country_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:2:"11";}}'
            ]
        );

        $setup->getConnection()->insertOnDuplicate(
            $setup->getConnection()->getTableName("core_config_data"),
            [
                "scope" =>"stores",
                "scope_id" => 7,
                "path" => "customer/address_attributes_order/address_attributes_positions",
                "value" => 'a:12:{s:18:"_1635247317684_684";a:4:{s:9:"attribute";s:9:"firstname";s:19:"individual_position";s:2:"10";s:16:"company_position";s:1:"9";s:16:"default_position";s:1:"1";}s:18:"_1635247321258_258";a:4:{s:9:"attribute";s:8:"lastname";s:19:"individual_position";s:2:"20";s:16:"company_position";s:2:"10";s:16:"default_position";s:1:"2";}s:18:"_1635247324899_899";a:4:{s:9:"attribute";s:6:"region";s:19:"individual_position";s:2:"30";s:16:"company_position";s:1:"4";s:16:"default_position";s:1:"8";}s:18:"_1635247338415_415";a:4:{s:9:"attribute";s:4:"city";s:19:"individual_position";s:2:"40";s:16:"company_position";s:1:"5";s:16:"default_position";s:1:"9";}s:18:"_1635247343218_218";a:4:{s:9:"attribute";s:6:"street";s:19:"individual_position";s:2:"50";s:16:"company_position";s:1:"6";s:16:"default_position";s:1:"6";}s:18:"_1635247349442_442";a:4:{s:9:"attribute";s:8:"postcode";s:19:"individual_position";s:2:"60";s:16:"company_position";s:1:"7";s:16:"default_position";s:2:"10";}s:18:"_1635247362379_379";a:4:{s:9:"attribute";s:9:"telephone";s:19:"individual_position";s:2:"70";s:16:"company_position";s:1:"8";s:16:"default_position";s:1:"4";}s:18:"_1635247378717_717";a:4:{s:9:"attribute";s:7:"company";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"1";s:16:"default_position";s:1:"3";}s:18:"_1635247382835_835";a:4:{s:9:"attribute";s:11:"pfpj_reg_no";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"2";s:16:"default_position";s:0:"";}s:18:"_1635247389511_511";a:4:{s:9:"attribute";s:6:"vat_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"3";s:16:"default_position";s:1:"7";}s:18:"_1635247515663_663";a:4:{s:9:"attribute";s:3:"fax";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"5";}s:18:"_1635247567513_513";a:4:{s:9:"attribute";s:10:"country_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:2:"11";}}'
            ]
        );

        $setup->getConnection()->insertOnDuplicate(
            $setup->getConnection()->getTableName("core_config_data"),
            [
                "scope" =>"stores",
                "scope_id" => 5,
                "path" => "customer/address_attributes_order/address_attributes_positions",
                "value" => 'a:12:{s:18:"_1635247621219_219";a:4:{s:9:"attribute";s:9:"firstname";s:19:"individual_position";s:2:"10";s:16:"company_position";s:1:"8";s:16:"default_position";s:1:"1";}s:18:"_1635247623947_947";a:4:{s:9:"attribute";s:8:"lastname";s:19:"individual_position";s:2:"20";s:16:"company_position";s:1:"9";s:16:"default_position";s:1:"2";}s:18:"_1635247627425_425";a:4:{s:9:"attribute";s:6:"street";s:19:"individual_position";s:2:"30";s:16:"company_position";s:1:"3";s:16:"default_position";s:1:"6";}s:18:"_1635247631307_307";a:4:{s:9:"attribute";s:4:"city";s:19:"individual_position";s:2:"40";s:16:"company_position";s:1:"4";s:16:"default_position";s:1:"9";}s:18:"_1635247634866_866";a:4:{s:9:"attribute";s:8:"postcode";s:19:"individual_position";s:2:"50";s:16:"company_position";s:1:"5";s:16:"default_position";s:2:"10";}s:18:"_1635247639612_612";a:4:{s:9:"attribute";s:9:"telephone";s:19:"individual_position";s:2:"60";s:16:"company_position";s:1:"6";s:16:"default_position";s:1:"4";}s:18:"_1635247658773_773";a:4:{s:9:"attribute";s:7:"company";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"1";s:16:"default_position";s:1:"3";}s:18:"_1635247663421_421";a:4:{s:9:"attribute";s:11:"pfpj_reg_no";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"2";s:16:"default_position";s:0:"";}s:18:"_1635247696419_419";a:4:{s:9:"attribute";s:6:"vat_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:1:"7";s:16:"default_position";s:1:"7";}s:18:"_1635247729507_507";a:4:{s:9:"attribute";s:3:"fax";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"5";}s:17:"_1635247759068_68";a:4:{s:9:"attribute";s:6:"region";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:1:"8";}s:18:"_1635247776649_649";a:4:{s:9:"attribute";s:10:"country_id";s:19:"individual_position";s:0:"";s:16:"company_position";s:0:"";s:16:"default_position";s:2:"11";}}'
            ]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function addNewOrderAttributeParibas(ModuleDataSetupInterface $setup)
    {
        $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);
        $salesSetup->addAttribute(Order::ENTITY, OrderAttributeEnum::ORDER_ATTRIBUTE_PARIBAS_PIN, [
            'type' => Table::TYPE_TEXT,
            'length'=> 255,
            'visible' => false,
            'nullable' => true
        ]);

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('sales_order'),
                OrderAttributeEnum::ORDER_ATTRIBUTE_PARIBAS_PIN,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'BNP Paribas PIN'
                ]
            );

    }


    private function addProductDistributorAttribute(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'distributor',
            [
                'type'                    => 'text',
                'backend'                 => '',
                'frontend'                => '',
                'label'                   => 'Distributor',
                'input'                   => 'text',
                'class'                   => '',
                'source'                  => '',
                'global'                  => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible'                 => false,
                'required'                => false,
                'user_defined'            => false,
                'default'                 => '',
                'searchable'              => false,
                'filterable'              => false,
                'comparable'              => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => false,
                'unique'                  => false,
                'apply_to'                => ''
            ]
        );
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
            'type'         => 'varchar',
            'label'        => 'Registration Number',
            'input'        => 'text',
            'required'     => false,
            'visible'      => false,
            'user_defined' => false,
            'system'       => false,
            'sort_order'   => 60,
            'position'     => 60,
        ];
        $eavSetup->addAttribute(
            'customer_address',
            $attributeCode,
            $attributeParams
        );
        $quoteSetup->addAttribute('quote_address', $attributeCode, $attributeParams);
        $salesSetup->addAttribute('order_address', $attributeCode, $attributeParams);

        $customerAddress = $eavSetup->getEntityTypeId('customer_address');
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
            ->insertMultiple($eavSetup->getSetup()->getTable('customer_form_attribute'), $data);
    }

    /**
     * Add item_group + item_type attributes
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function addItemGroupAndItemTypeAttributes(ModuleDataSetupInterface $setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'item_group',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'ItemGroup',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'item_type',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'ItemType',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => ''
            ]
        );
    }
}
