<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Model\Resource\Donor;

use Oander\ImportM2\Model\Resource\Donor;

/**
 * Class CategoryDonor
 *
 * @package Oander\ImportM2\Model\Resource\Donor
 */
class CategoryDonor extends Donor
{
    /**
     * @return array
     */
    public function getCategoryVarcharAttributes($storeIds)
    {
        $sql = $this->donorConnection->select()
            ->from('catalog_category_entity_varchar')
            ->where('store_id IN (?)', $storeIds);

        return $this->donorConnection->fetchAll($sql);
    }


}
