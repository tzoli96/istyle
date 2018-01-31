<?php
/**
 * Oander_IndexerRangeBugFix
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IndexerRangeBugFix\Magento\Catalog\Model\Indexer\Category\Product\Action;

use Magento\Catalog\Model\Indexer\Category\Product\Action\Full as MagentoFull;

/**
 * Class Full
 * @package Oander\IndexerRangeBugFix\Magento\Catalog\Model\Indexer\Category\Product\Action
 */
class Full extends MagentoFull
{
    /**
     * Check whether select ranging is needed
     *
     * @return bool
     */
    protected function isRangingNeeded()
    {
        return false;
    }
}
