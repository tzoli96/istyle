<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Popup\Controller\Adminhtml\Popup;

/**
 * Class MassDelete
 * @package Aheadworks\Popup\Controller\Adminhtml\Popup
 */
class MassDelete extends \Aheadworks\Popup\Controller\Adminhtml\Popup
{
    /**
     * Popup collection
     * @var \Aheadworks\Popup\Model\ResourceModel\Popup\Collection
     */
    private $collection;

    /**
     * Massaction filter
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    private $filter;

    /**
     * Popup model factory
     * @var \Aheadworks\Popup\Model\PopupFactory
     */
    private $popupModelFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Aheadworks\Popup\Model\PopupFactory $popupModelFactory
     * @param \Aheadworks\Popup\Model\ResourceModel\Popup\Collection $collection
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Aheadworks\Popup\Model\PopupFactory $popupModelFactory,
        \Aheadworks\Popup\Model\ResourceModel\Popup\Collection $collection
    ) {
        parent::__construct($context);
        $this->collection = $collection;
        $this->filter = $filter;
        $this->popupModelFactory = $popupModelFactory;
    }

    /**
     * Mass delete popups
     *
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->collection = $this->filter->getCollection($this->collection);
        $count = 0;
        foreach ($this->collection->getItems() as $popup) {
            $popupModel = $this->popupModelFactory->create();
            $popupModel->load($popup->getId());
            if ($popupModel->getId()) {
                $popupModel->delete();
                $count++;
            }
        }
        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been deleted.', $count)
        );
        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
