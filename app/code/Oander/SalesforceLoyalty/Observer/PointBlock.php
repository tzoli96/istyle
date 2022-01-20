<?php

namespace Oander\SalesforceLoyalty\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Oander\SalesforceLoyalty\Enum\Attribute;

class PointBlock implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Oander\SalesforceLoyalty\Helper\Salesforce
     */
    private $salesforceHelper;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper
    )
    {

        $this->messageManager = $messageManager;
        $this->salesforceHelper = $salesforceHelper;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($order->getData(Attribute::LOYALTY_DISCOUNT)) {
            try {
                $transactionId = $this->salesforceHelper->blockCustomerAffiliatePoints($order->getData(Attribute::LOYALTY_POINT));
                if($transactionId)
                    $order->setData(Attribute::LOYALTY_BLOCK_TRANSACTION_ID,$transactionId);
                else
                    $this->messageManager->addErrorMessage(__("Affiliate Points can not be blocked"));
            }
            catch (\Oander\Salesforce\Exception\RESTResponseException $exception)
            {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
            catch (\Oander\Salesforce\Exception\RESTException $exception)
            {
                $this->messageManager->addErrorMessage(__("Affiliate Points can not be blocked"));
            }
            catch (\Exception $exception)
            {
                $this->messageManager->addErrorMessage(__("Server Error in Loyalty"));
            }
        }
    }
}