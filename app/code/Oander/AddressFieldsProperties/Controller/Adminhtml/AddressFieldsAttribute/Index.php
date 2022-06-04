<?php

namespace Oander\AddressFieldsProperties\Controller\Adminhtml\AddressFieldsAttribute;

use Magento\Backend\App\Action\Context as Context;
use Magento\Framework\Controller\ResultInterface as ResultInterface;
use Magento\Framework\View\Result\PageFactory as PageFactory;

class Index extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = "Oander_AddressFieldsProperties::addressFieldsAttribute_update";

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Address Fields"));
        return $resultPage;
    }
}