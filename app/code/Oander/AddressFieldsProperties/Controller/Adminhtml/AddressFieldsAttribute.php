<?php

namespace Oander\AddressFieldsProperties\Controller\Adminhtml;

abstract class AddressFieldsAttribute extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Oander_AddressFieldsProperties::top_level';
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Oander'), __('Oander'))
            ->addBreadcrumb(__('Address Fields Attribute'), __('Address Fields Attribute'));
        return $resultPage;
    }
}
