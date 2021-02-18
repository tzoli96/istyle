<?php
namespace Oander\HelloBankPayment\Controller\Adminhtml\Barem;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Oander\HelloBankPayment\Api\Data\BaremRepositoryInterface;
use Oander\HelloBankPayment\Controller\Adminhtml\Grid;
use Oander\HelloBankPayment\Model\BaremsFactory;
use Oander\HelloBankPayment\Model\Barems;
use Oander\HelloBankPayment\Enum\Request;

class Save extends Grid
{
    /**
     * @var BaremsFactory
     */
    private $baremsFactory;

    /**
     * @var
     */
    private $model;

    public function __construct(
        BaremsFactory $baremsFactory,
        BaremRepositoryInterface $baremRepository,
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->model = false;
        $this->baremsFactory = $baremsFactory;
        parent::__construct($baremRepository, $context, $resultPageFactory);
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->getRequest()->getPostValue()) {
            return $resultRedirect->setPath(Request::ACTION_BAREM_GRID_INDEX);
        }
        $data = (array)$this->getRequest()->getPostValue();

        if($data)
        {
            try {
                $model = $this->baremsFactory->create();
                $model->setData($data);
                $this->baremRepository->save($model);
                $this->model = $model;

                $this->messageManager->addSuccessMessage(__('Barem has been successfully saved.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } finally {
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                if ($this->getRequest()->getParam(Request::PARAM_BACK, false)) {
                     $resultRedirect->setPath(Request::URL_EDIT_PATH, [BaremInterface::ID => $this->model->getId()]);
                } else {
                     $resultRedirect->setPath(Request::ACTION_BAREM_GRID_INDEX);
                }
            }
        }

        return $resultRedirect;
    }
}