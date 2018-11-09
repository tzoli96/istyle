<?php
/**
 *   /$$$$$$   /$$$$$$  /$$   /$$ /$$$$$$$  /$$$$$$$$ /$$$$$$$
 *  /$$__  $$ /$$__  $$| $$$ | $$| $$__  $$| $$_____/| $$__  $$
 * | $$  \ $$| $$  \ $$| $$$$| $$| $$  \ $$| $$      | $$  \ $$
 * | $$  | $$| $$$$$$$$| $$ $$ $$| $$  | $$| $$$$$   | $$$$$$$/
 * | $$  | $$| $$__  $$| $$  $$$$| $$  | $$| $$__/   | $$__  $$
 * | $$  | $$| $$  | $$| $$\  $$$| $$  | $$| $$      | $$  \ $$
 * |  $$$$$$/| $$  | $$| $$ \  $$| $$$$$$$/| $$$$$$$$| $$  | $$
 *  \______/ |__/  |__/|__/  \__/|_______/ |________/|__/  |__/
 *
 * Oander_IstyleBase
 *
 * @author  David Belicza <david.belicza@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\IstyleBase\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Cms\Model\Block;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Oander\Base\Model\Setup\ResourceReaderTrait;
use Oander\IstyleBase\Pricing\OldPrice;

/**
 * Class UpgradeData
 *
 * @package Oander\IstyleBase\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    use ResourceReaderTrait;

    /**
     * @var BlockInterfaceFactory
     */
    private $blockFactory;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * UpgradeData constructor.
     *
     * @param BlockInterfaceFactory    $blockFactory
     * @param BlockRepositoryInterface $blockRepository
     * @param EavSetup                 $eavSetup
     */
    public function __construct(
        BlockInterfaceFactory $blockFactory,
        BlockRepositoryInterface $blockRepository,
        EavSetup $eavSetup
    ) {
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
        $this->eavSetup = $eavSetup;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion()
            && version_compare($context->getVersion(), '1.0.1') < 0
        ) {
            $this->upgrade_1_0_1($setup);
        }

        $setup->endSetup();
    }


    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function upgrade_1_0_1(ModuleDataSetupInterface $setup)
    {
        /** @var BlockInterface|Block $block */
        $block = $this->blockFactory->create();

        $content = $this->getTextResource(
            'Oander_IstyleBase',
            'resource/mail-chimp-form-v1.txt'
        );

        $data = [
            'title'      => 'MailChimp Newsletter footer place',
            'identifier' => 'mail_chimp_footer',
            'stores'     => [0],
            'is_active'  => 1,
            'content'    => $content
        ];

        $block->setData($data);

        $this->blockRepository->save($block);
    }
}
