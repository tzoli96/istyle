<?php


namespace Oney\ThreeByFour\Controller\Index;


use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\ResponseInterface;

class Index extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
