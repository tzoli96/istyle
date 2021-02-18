<?php
namespace Oander\HelloBankPayment\Controller\Adminhtml\Barem;

use Oander\HelloBankPayment\Controller\Adminhtml\Grid;

class Edit extends Grid
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        $this->initPage($resultPage)
            ->getConfig()
            ->getTitle()
            ->prepend(__('Edit Barem'));

        return $resultPage;

    }
}