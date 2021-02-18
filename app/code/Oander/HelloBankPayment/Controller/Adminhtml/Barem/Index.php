<?php
namespace Oander\HelloBankPayment\Controller\Adminhtml\Barem;

use Oander\HelloBankPayment\Controller\Adminhtml\Grid;
use Magento\Backend\Model\View\Result\Page;

class Index extends Grid
{
    /**
     * @return Page
     */
    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Barems'));

        return $resultPage;
    }
}