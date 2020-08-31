<?php
namespace Aheadworks\Popup\Model\ResourceModel\EntityRelation;

use Magento\Framework\Model\AbstractModel;

/**
 * Interface HandlerInterface
 * @package Aheadworks\Popup\Model\ResourceModel\EntityRelation
 */
interface HandlerInterface
{
    /**
     * After save operation
     *
     * @param AbstractModel $entity
     * @return void
     */
    public function afterSave($entity);

    /**
     * After load operation
     *
     * @param AbstractModel $entity
     * @return void
     */
    public function afterLoad($entity);
}
