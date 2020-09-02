<?php
namespace Aheadworks\Popup\Controller\Adminhtml\Popup;

/**
 * Class Index
 * @package Aheadworks\Popup\Controller\Adminhtml\Popup
 */
class Index extends \Aheadworks\Popup\Controller\Adminhtml\Popup
{
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        return $resultPage;
    }
}
