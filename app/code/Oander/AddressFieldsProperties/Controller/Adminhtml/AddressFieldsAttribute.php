<?php

namespace Oander\AddressFieldsProperties\Controller\Adminhtml;

use Magento\Backend\App\Action\Context as Context;
use Magento\Backend\Model\View\Result\Page as Page;
use Magento\Framework\Registry as Registry;

abstract class AddressFieldsAttribute extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Oander_AddressFieldsProperties::top_level';
    protected $_coreRegistry;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context                     $context,
        Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param Page $resultPage
     * @return Page
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Oander'), __('Oander'))
            ->addBreadcrumb(__('Address Fields Attribute'), __('Address Fields Attribute'));
        return $resultPage;
    }
}
