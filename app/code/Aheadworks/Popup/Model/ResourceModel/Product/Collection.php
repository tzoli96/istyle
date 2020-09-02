<?php

namespace Aheadworks\Popup\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Collection
 * @package Aheadworks\Popup\Model\Resource\Product
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Overwrite parent getAllIds method. Delete resetJoinLeft.
     *
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('e.entity_id');
        $idsSelect->limit($limit, $offset);
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }
}
