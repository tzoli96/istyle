<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Oander\Autorelated\Block;

use Aheadworks\Autorelated\Model\Source\Position;
use Aheadworks\Autorelated\Model\Source\Type;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Related
 *
 * @package Oander\Autorelated\Block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Related extends \Aheadworks\Autorelated\Block\Related implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Oander_Autorelated::block.phtml';

    /**
     * @var int|null
     */
    private $blockPosition;

    /**
     * @var int|null
     */
    private $blockType = Type::PRODUCT_BLOCK_TYPE;

    /**
     * @var array|null
     */
    private $blocks;

    /**
     * Return blocks for current block position and type
     *
     * @return \Aheadworks\Autorelated\Api\Data\BlockInterface[]
     */
    public function getBlocks()
    {
        if (null === $this->blocks) {
            $this->blocks = $this->blocksRepository
                ->getList(
                    $this->getBlockType(),
                    $this->getBlockPosition(),
                    true
                )->getItems();
        }

        return $this->blocks;
    }

    /**
     * Return block position
     *
     * @return int|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getBlockPosition()
    {
        if ($this->blockPosition === null) {
            if($this->getNameInLayout() == "awarp_content_dropdown_bottom") {
                $this->blockPosition = \Oander\Autorelated\Enum\BlockPosition::DROPDOWN_PROMO_BOTTOM;
            }
        }
        return $this->blockPosition;
    }

    /**
     * Rreturn block type
     *
     * @return int|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getBlockType()
    {
        return $this->blockType;
    }
}
