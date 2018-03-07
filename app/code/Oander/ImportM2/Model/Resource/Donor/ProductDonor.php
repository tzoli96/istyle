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
}
