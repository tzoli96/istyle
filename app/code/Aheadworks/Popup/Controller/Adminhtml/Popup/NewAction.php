<?php
namespace Aheadworks\Popup\Controller\Adminhtml\Popup;

/**
 * Class NewAction
 * @package Aheadworks\Popup\Controller\Adminhtml\Popup
 */
class NewAction extends \Aheadworks\Popup\Controller\Adminhtml\Popup
{
    /**
     * Result forward factory
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    private $resultForwardFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
