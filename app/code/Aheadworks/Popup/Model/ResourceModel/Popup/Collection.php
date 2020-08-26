<?php
/**
 * Aheadworks Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://ecommerce.aheadworks.com/end-user-license-agreement/
 *
 * @package    Popup
 * @version    1.2.2
 * @copyright  Copyright (c) 2020 Aheadworks Inc. (http://www.aheadworks.com)
 * @license    https://ecommerce.aheadworks.com/end-user-license-agreement/
 */














































namespace Aheadworks\Popup\Model\ResourceModel\Popup;

use Aheadworks\Popup\Model\Popup;
use Aheadworks\Popup\Model\Source\Event;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Aheadworks\Popup\Model\ResourceModel\Popup as PopupResource;

/**
 * Class Collection
 * @package Aheadworks\Popup\Model\ResourceModel\Popup
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends AbstractCollection
{
    /**
     * Id field name
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * {@inheritDoc}
     */
    public function _construct()
    {
        $this->_init(Popup::class, PopupResource::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        if (!$this->getFlag('ctr_joined')) {
            $this->getSelect()
                ->joinLeft(
                    ['ctr_t' => new \Zend_Db_Expr(
                        "(Select " .
                        "id as ctr_id, " .
                        "ROUND(click_count/IF(view_count > 0, view_count, 1) * 100, 0) as ctr ".
                        "FROM {$this->getTable('aw_popup_block')})"
                    )
                    ],
                    "main_table.id = ctr_t.ctr_id",
                    ['ctr']
                );
            $this->addFilterToMap('ctr', 'ctr_t.ctr');
            $this->setFlag('ctr_joined', true);
        }

        return $this;
    }

    /**
     * Add customer group filter
     *
     * @param array $customerGroups
     * @return $this
     */
    public function addCustomerGroupFilter($customerGroups)
    {
        $this->addFieldToFilter('customer_groups', ['finset' => $customerGroups]);
        return $this;
    }

    /**
     * Add position filter
     *
     * @param int $position
     * @return $this
     */
    public function addPositionFilter($position)
    {
        $this->addFieldToFilter('position', ['eq' => $position]);
        return $this;
    }

    /**
     * Add page type filter
     *
     * @param int $page
     * @return $this
     */
    public function addPageTypeFilter($page)
    {
        $this->addFieldToFilter('page_type', ['finset' => $page]);
        return $this;
    }

    /**
     * Add store filter
     *
     * @param int $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $this
            ->getSelect()
            ->where("FIND_IN_SET(0, store_ids) OR FIND_IN_SET({$storeId}, store_ids)");
        return $this;
    }

    /**
     * Add status enabled filter
     *
     * @return $this
     */
    public function addStatusEnabledFilter()
    {
        $this->addFieldToFilter('status', ['eq' => 1]);
        return $this;
    }

    /**
     * Add excluded ids filter
     *
     * @param array $popupIds
     * @return $this
     */
    public function addExcludedIdsFilter(array $popupIds)
    {
        $this->addFieldToFilter('main_table.id', ['nin' => $popupIds]);
        return $this;
    }

    /**
     * Add pages viewed filter
     *
     * @param int $viewedCount
     * @return $this
     */
    public function addPageViewedFilter($viewedCount)
    {
        $eventPageViewedType = Event::VIEWED_PAGES;
        $this
            ->getSelect()
            ->where(
                "(main_table.event <> '" . $eventPageViewedType . "' OR (main_table.event = '" . $eventPageViewedType .
                "' AND main_table.event_value <= " . $viewedCount . "))"
            );
        return $this;
    }

    /**
     * Add customer segment filter filter
     *
     * @param int $customerId
     * @param int $storeId
     * @return $this
     */
    public function addCustomerSegmentFilter($customerId, $storeId)
    {
        $segmentsCountExpr = new \Zend_Db_Expr('COUNT(popup_segment.popup_id)');
        $validSegmentsCountExpr = new \Zend_Db_Expr('SUM(IF(customer_segment_index.customer_id = ' . $customerId
            . ' AND segment.is_enabled = 1 AND customer_segment_index.store_id = ' . $storeId . ' , 1, 0))');

        $select = $this->getConnection()
            ->select()
            ->from(
                ['main_table' => $this->getTable('aw_popup_block')],
                [
                    'main_table.id',
                    'segments_count' => $segmentsCountExpr,
                    'valid_segments_count' => $validSegmentsCountExpr
                ]
            )->joinLeft(
                ['popup_segment' => $this->getTable('aw_popup_block_segment')],
                'popup_segment.popup_id = main_table.id',
                []
            )->joinLeft(
                ['segment' => $this->getTable('aw_customer_segmentation_segment')],
                'segment.segment_id = popup_segment.segment_id',
                []
            )->joinLeft(
                ['customer_segment_index' => $this->getTable('aw_customer_segment_customer_index')],
                'popup_segment.segment_id = customer_segment_index.segment_id',
                []
            )->group('main_table.id');

        $this->getSelect()
            ->joinLeft(
                ['popup_segment' => $select],
                'popup_segment.id = main_table.id',
                []
            )->where(
                '(popup_segment.segments_count > 0 AND popup_segment.valid_segments_count > 0)'
                . ' OR (popup_segment.segments_count = 0)'
            );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function _afterLoad()
    {
        $this->attachSegmentsData();

        return parent::_afterLoad();
    }

    /**
     * Attach customer segments data
     *
     * @return $this
     */
    private function attachSegmentsData()
    {
        $select = $this->getConnection()->select()->from($this->getTable('aw_popup_block_segment'));
        $segmentsData = $this->getConnection()->fetchAll($select);

        foreach ($this->getItems() as $item) {
            $dataToAttach = [];
            $popupId = $item->getId();
            foreach ($segmentsData as $segmentData) {
                if ($segmentData['popup_id'] == $popupId) {
                    $dataToAttach[] = $segmentData['segment_id'];
                }
            }
            $item->setCustomerSegments($dataToAttach);
        }

        return $this;
    }
}
