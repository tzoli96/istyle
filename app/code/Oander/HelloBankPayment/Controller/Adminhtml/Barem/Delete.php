<?php
namespace Oander\HelloBankPayment\Controller\Adminhtml\Barem;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Oander\HelloBankPayment\Controller\Adminhtml\Grid;
use Oander\HelloBankPayment\Enum\Request;
use Oander\HelloBankPayment\Api\Data\BaremInterface;

class Delete extends Grid
{

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam(BaremInterface::ID);

        if ($id) {
            try {
                $this->baremRepository->delete($id);
                $this->messageManager->addSuccessMessage(__('Barem has been deleted'));

                return $resultRedirect->setPath(Request::ACTION_BAREM_GRID_INDEX);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath(Request::ACTION_BAREM_GRID_INDEX);
            }
        }

        $this->messageManager->addErrorMessage(__('Barem does not exist'));

        return $resultRedirect->setPath('*/*/');
    }
}