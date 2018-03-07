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
 * Class UrlRewriteDonor
 *
 * @package Oander\ImportM2\Model\Resource\Donor
 */
class UrlRewriteDonor extends Donor
{
    /**
     * @return array
     */
    public function getProductUrlRewrites($storeIds, $productIds = [])
    {
        $sql = $this->donorConnection->select()
            ->from('url_rewrite')
            ->joinInner(
                ['catalog_product_entity', $this->donorConnection->getTableName('catalog_product_entity')],
                'catalog_product_entity.entity_id = url_rewrite.entity_id',
                ['sku', 'type_id']
            )
            ->where('entity_type = ?','product')
           // ->where('entity_ids IN (?)',$productIds)
            ->where('store_id IN (?)', $storeIds);

        return $this->donorConnection->fetchAll($sql);
    }

    /**
     * @return array
     */
    public function getCategoryRewrites($storeId, $categoryName = "")
    {
        $sql = $this->donorConnection->select()
            ->from('url_rewrite')
            ->joinInner(
                ['catalog_category_entity_varchar', $this->donorConnection->getTableName('catalog_category_entity_varchar')],
                'catalog_category_entity_varchar.entity_id = url_rewrite.entity_id
                    AND catalog_category_entity_varchar.store_id = '.$storeId.'
                    AND catalog_category_entity_varchar.attribute_id = 45',
                ['value']
            )
            ->where('catalog_category_entity_varchar.value = ?',$categoryName)
            ->where('url_rewrite.entity_type = ?','category')
            ->where('url_rewrite.store_id = ?', $storeId);

        return $this->donorConnection->fetchAll($sql);
    }

}
