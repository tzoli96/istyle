<?php
namespace Oander\HelloBankPayment\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

use Oander\HelloBankPayment\Api\Data\BaremRepositoryInterface;

abstract class Grid extends Action
{
    /**
     * @var BaremRepositoryInterface
     */
    protected $baremRepository;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param BaremRepositoryInterface $baremRepository
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        BaremRepositoryInterface $baremRepository,
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->baremRepository = $baremRepository;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     *
     * @param Page $resultPage
     * @return Page
     */
    protected function initPage(Page $resultPage): Page
    {
        $resultPage->setActiveMenu('Oander_HelloBankPayment::barems')
            ->addBreadcrumb(__('HelloBank'), __('HelloBank'))
            ->addBreadcrumb(__('Grids'), __('Grids'));
        return $resultPage;
    }
}