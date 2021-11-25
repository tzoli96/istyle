<?php
namespace Oander\Cleaner\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Oander\Cleaner\Model\Service\Execute;

class DemoTest extends AbstractAjax
{
    /**
     * @var Execute
     */
    protected $execute;

    /**
     * ErpTest constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Execute $execute
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Execute $execute
    ){
        $this->execute = $execute;
        parent::__construct($context, $resultJsonFactory);
    }

    /**
     * @return array
     */
    public function executeForJson()
    {
        $response = [];
        try {
                $response['success'] = true;
                $response['response'] = $this->execute->execute();
                return $response;
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
            return $response;
        }
    }


}
