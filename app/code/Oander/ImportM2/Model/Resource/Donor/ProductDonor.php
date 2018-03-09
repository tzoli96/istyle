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
 * Class StoreDonor
 *
 * @package Oander\ImportM2\Model\Resource\Donor
 */
class ProductDonor extends Donor
{
    /**
     * @return array
     */
    public function getAttributes()
    {
        $sql = $this->donorConnection->select()
            ->from('eav_attribute')
            ->where('entity_type_id = ?', 4)
            ->where('frontend_input IN (?)', ['select', 'multiselect']);

        return $this->donorConnection->fetchAll($sql);
    }

    /**->join
     *
     * @param $attributeId
     *
     * @return array
     */
    public function getAttributeOptions($attributeId, $storeIds)
    {
        $sql = $this->donorConnection->select()
            ->from('eav_attribute_option')
            ->joinInner(
                ['eav_attribute_option_value', $this->donorConnection->getTableName('eav_attribute_option_value')],
                'eav_attribute_option.option_id = eav_attribute_option_value.option_id',
                ['value_id', 'value', 'store_id']
            )
            ->where('attribute_id = ?', $attributeId)
            ->where('store_id IN (?)', $storeIds);

        return $this->donorConnection->fetchAll($sql);
    }

    public function getAttributeLabels($attributeCode, $storeIds)
    {

        $sql = $this->donorConnection->select()
            ->from('eav_attribute')
            ->joinInner(
                ['eav_attribute_label', $this->donorConnection->getTableName('eav_attribute_label')],
                'eav_attribute_label.attribute_id = eav_attribute.attribute_id',
                ['value','store_id']
            )
            ->where('eav_attribute_label.store_id IN (?)', $storeIds)
            ->where('eav_attribute.entity_type_id = ?', 4)
            ->where('eav_attribute.attribute_code = ?', $attributeCode);

        return $this->donorConnection->fetchAll($sql);
    }

    public function getSuperAttributes()
    {
        $sql = $this->donorConnection->select()
            ->from('catalog_product_super_attribute')
            ->joinInner(
                ['catalog_product_entity', $this->donorConnection->getTableName('catalog_product_entity')],
                'catalog_product_entity.entity_id = catalog_product_super_attribute.product_id',
                ['sku']
            )->joinInner(
                ['eav_attribute', $this->donorConnection->getTableName('eav_attribute')],
                'eav_attribute.attribute_id = catalog_product_super_attribute.attribute_id',
                ['attribute_code']
            );

        return $this->donorConnection->fetchAll($sql);
    }

    public function getIdSku()
    {
        $sql = $this->donorConnection->select()
            ->from('catalog_product_entity');

        $productEntities = $this->donorConnection->fetchAll($sql);
        $skuId = [];
        foreach ($productEntities as $productEntity) {
            $skuId[$productEntity['entity_id']] = $productEntity['sku'];
        }

        return $skuId;
    }

    public function getSuperLinks()
    {
        $sql = $this->donorConnection->select()
            ->from('catalog_product_super_link');

        return $this->donorConnection->fetchAll($sql);
    }
}
