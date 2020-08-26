<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Popup\Model\Source;

use Aheadworks\Popup\Model\ThirdPartyModule\Manager;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class CustomerSegments
 * @package Aheadworks\Popup\Model\Source
 */
class CustomerSegments implements OptionSourceInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Manager $moduleManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Manager $moduleManager
    ) {
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        $options = [];

        if ($this->moduleManager->isCustomerSegmentationModuleEnabled()) {
            $collection = $this->getSegmentsCollection();
            $collection->addOrder('name', SortOrder::SORT_ASC);
            /** @var \Aheadworks\CustomerSegmentation\Api\Data\SegmentInterface $segment */
            foreach ($collection->getItems() as $segment) {
                $options[] = [
                    'value' => $segment->getSegmentId(),
                    'label' => $segment->getIsEnabled()
                        ? $segment->getName()
                        : $segment->getName() . __('(Disabled)')
                ];
            }
        }

        return $options;
    }

    /**
     * Retrieve segments collection
     *
     * @return \Aheadworks\CustomerSegmentation\Model\ResourceModel\Segment\Collection
     */
    private function getSegmentsCollection()
    {
        return $this->objectManager->create(
            \Aheadworks\CustomerSegmentation\Model\ResourceModel\Segment\Collection::class
        );
    }
}
