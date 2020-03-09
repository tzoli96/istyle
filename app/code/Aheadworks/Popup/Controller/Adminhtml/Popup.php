<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Popup\Controller\Adminhtml;

/**
 * Class Popup
 * @package Aheadworks\Popup\Controller\Adminhtml
 */
abstract class Popup extends \Magento\Backend\App\Action
{
    /**
     * Result page factory
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Init action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Aheadworks_Popup::main');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Popups'));
        $resultPage->getLayout()->getBlock('aw_popup.menu')->setCurrentItemKey(
            \Aheadworks\Popup\Block\Adminhtml\Menu::ITEM_BLOCK
        );

        return $resultPage;
    }

    /**
     * Is access to section allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aheadworks_Popup::popup');
    }
}
