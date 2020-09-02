<?php
namespace Aheadworks\Popup\Model\ResourceModel\EntityRelation\Handler;

use Aheadworks\Popup\Model\Popup;
use Aheadworks\Popup\Model\ResourceModel\EntityRelation\HandlerInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Class CustomerSegments
 * @package Aheadworks\Popup\Model\ResourceModel\EntityRelation\Handler
 */
class CustomerSegments implements HandlerInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritDoc}
     * @param Popup $entity
     */
    public function afterSave($entity)
    {
        if ($entity->getId()) {
            $this->deleteByEntity($entity->getId());
            $dataToSave = $this->getDataToSave($entity);
            $this->saveData($dataToSave);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function afterLoad($entity)
    {
        if ($entity->getId()) {
            $segments = $this->getSegmentsData($entity);
            $entity->setCustomerSegments($segments);
        }
    }

    /**
     * Get table name
     *
     * @return string
     */
    private function getTableName()
    {
        return $this->resourceConnection->getTableName('aw_popup_block_segment');
    }

    /**
     * Remove data
     *
     * @param int $popupId
     * @return int
     */
    private function deleteByEntity($popupId)
    {
        return $this->resourceConnection->getConnection()->delete(
            $this->getTableName(),
            ['popup_id = ?' => $popupId]
        );
    }

    /**
     * Retrieve data to save in the corresponding table
     *
     * @param Popup $entity
     * @return array
     */
    private function getDataToSave($entity)
    {
        $data = [];
        $popupId = $entity->getId();

        foreach ((array)$entity->getCustomerSegments() as $segmentId) {
            $data[] = [
                'popup_id' => $popupId,
                'segment_id' => $segmentId
            ];
        }

        return $data;
    }

    /**
     * Save data in the corresponding table
     *
     * @param array $dataToSave
     * @return $this
     */
    private function saveData($dataToSave)
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->getTableName();
            $connection->insertMultiple(
                $tableName,
                $dataToSave
            );
        } catch (\Exception $exception) {
            return $this;
        }

        return $this;
    }

    /**
     * Retrieve segments data
     *
     * @param Popup $entity
     * @return array
     */
    public function getSegmentsData($entity)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->getTableName(), ['segment_id'])
            ->where('popup_id = ?', $entity->getId());

        return $connection->fetchCol($select);
    }
}
