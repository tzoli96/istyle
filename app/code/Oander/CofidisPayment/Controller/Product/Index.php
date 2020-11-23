<?php
namespace Oander\CofidisPayment\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
 
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;
 
 
    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, JsonFactory $resultJsonFactory)
    {
 
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
 
        parent::__construct($context);
    }
 
 
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();

        $shopId = $this->getRequest()->getParam('shopId');
        $barem = $this->getRequest()->getParam('barem');
        $amount = $this->getRequest()->getParam('amount');
        $downpmnt = $this->getRequest()->getParam('downpmnt');
        $month = $this->getRequest()->getParam('month');

        $postdata = array(
          'shopId'=> $shopId,
          'barem'=> $barem,
          'amount' => $amount,
          'downpmnt' => $downpmnt,
          'month' => $month,
        );

        $parameters=http_build_query($postdata);
        $ch = curl_init('https://www.cofidis.hu/calculatorweb/wcalc_eles/hidden/?' .
        $parameters);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $result->setData(['output' => curl_exec($ch)]);
    }
}
