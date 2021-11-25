<?php

namespace Oander\Cleaner\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

abstract class AbstractAjax extends Action
{
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    public function __construct(
        Context     $context,
        JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context);
        $this->_resultJsonFactory = $resultJsonFactory;
    }

    abstract protected function executeForJson();

    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $admin_session = 1;
        if (!$admin_session && $admin_session->getStatus() != 1) {
            throw new \Exception('Oops, this endpoint is for logged in admin and ajax only!');
        } else {
            try {
                $json = $this->executeForJson();
                return $result->setData($json);
            } catch (\Exception $e) {
                throw new Exception(
                    'Oops, there was error while processing your request.' .
                    ' Please contact admin for more details.'
                );
            }
        }
    }
}
