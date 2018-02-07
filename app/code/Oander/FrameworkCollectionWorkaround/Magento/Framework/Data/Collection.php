<?php
/**
 * Oander_FrameworkCollectionWorkaround
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FrameworkCollectionWorkaround\Magento\Framework\Data;

/**
 * Class Collection
 *
 * @package Oander\FrameworkCollectionWorkaround\Magento\Framework\Data
 */
class Collection extends \Magento\Framework\Data\Collection
{

    /**
     * Adding item to item array
     *
     * @param \Magento\Framework\Data\Collection $subject
     * @param callable                           $proceed
     * @param   \Magento\Framework\DataObject    $item
     *
     * @return $this
     * @throws \Exception
     */
    public function aroundAddItem(
        \Magento\Framework\Data\Collection $subject,
        callable $proceed,
        \Magento\Framework\DataObject $item
    ) {
        $itemId = $subject->_getItemId($item);
        if ($itemId !== null) {
            if (isset($subject->_items[$itemId])) {
                if (get_class($item) !== get_class($subject->_items[$itemId])) {
                    throw new \Exception(
                        'Item (' . get_class($item) . ') with the same ID "' . $item->getId() . '" already exists.'
                    );
                }
            }
            $subject->_items[$itemId] = $item;
        } else {
            $subject->_addItem($item);
        }

        return $subject;
    }
}
