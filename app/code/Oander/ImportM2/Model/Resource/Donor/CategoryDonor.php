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
    public function getCategoryAttribute($storeId,$categoryName,$attributeCode)
    {
        $attributeValue = null;
        $categoryAttributeTables = [
            'catalog_category_entity_varchar',
            'catalog_category_entity_int',
            'catalog_category_entity_datetime',
            'catalog_category_entity_decimal',
            'catalog_category_entity_text',
        ];

        $sqlN = $this->donorConnection->select()
            ->from('eav_attribute')
            ->where('attribute_code = ?','name')
            ->where('entity_type_id = ?', 3);
        $attributeNameData = $this->donorConnection->fetchRow($sqlN);

        $sql0 = $this->donorConnection->select()
            ->from('catalog_category_entity_varchar')
            ->where('store_id = ?',$storeId)
            ->where('attribute_id = ?', $attributeNameData['attribute_id'])
            ->where('value = ?', $categoryName);
        $categoryData = $this->donorConnection->fetchRow($sql0);

        if ($categoryData) {

            $sql = $this->donorConnection->select()
                ->from('eav_attribute')
                ->where('attribute_code = ?', $attributeCode)
                ->where('entity_type_id = ?', 3);
            $attributeData = $this->donorConnection->fetchRow($sql);

            if ($attributeData) {
                foreach ($categoryAttributeTables as $categoryAttributeTable) {
                    $sqlA = $this->donorConnection->select()
                        ->from($categoryAttributeTable)
                        ->where('attribute_id = ?', $attributeData['attribute_id'])
                        ->where('entity_id = ?', $categoryData['entity_id'])
                        ->where('store_id = ?', $storeId);
                    $attributeValueData = $this->donorConnection->fetchRow($sqlA);
                    if ($attributeValueData !== false && !empty($attributeValueData)) {
                        $attributeValue = $attributeValueData['value'];
                        break;
                    }
                }
            }
        }

        return $attributeValue;
    }

    public function getMissingCategories()
    {
        $sql = $this->donorConnection->select()
            ->from('catalog_category_entity')
            ->where("path LIKE '1/19%'");
        $categories = $this->donorConnection->fetchRow($sql);


    }


}
