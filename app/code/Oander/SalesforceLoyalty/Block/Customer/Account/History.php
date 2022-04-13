<?php

namespace Oander\SalesforceLoyalty\Block\Customer\Account;

use Magento\Customer\Model\Session\Proxy;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Oander\SalesforceLoyalty\Helper\Salesforce
     */
    private $salesforceHelper;
    /**
     * @var Proxy
     */
    private $customerSession;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Proxy $customerSession
     * @param \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Proxy                                            $customerSession,
        \Oander\SalesforceLoyalty\Helper\Salesforce      $salesforceHelper,
        \Magento\Sales\Model\OrderFactory                $orderFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->salesforceHelper = $salesforceHelper;
        $this->customerSession = $customerSession;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @return array|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLoyaltyPointsHistory()
    {
        $result = $this->salesforceHelper->getCustomerAffiliateTransactions($this->customerSession->getCustomer());
        foreach ($result['AffiliatedTransactions'] as $index => $item) {
            if ($item['MagentoOrderNumber']) {
                $order = $this->orderFactory->create()->loadByAttribute('increment_id', $item['MagentoOrderNumber']);
                $orderId = $order->getData('entity_id');
            } else {
                $orderId = false;
            }
            $result['AffiliatedTransactions'][$index]['OrderId'] = $orderId;
        }
        return $result;
    }

    /**
     * Retrieve script options encoded to json
     *
     * @return string
     */
    public function getScriptOptions()
    {
        $params = [
            'history' => $this->getLoyaltyPointsHistory(),
        ];

        return json_encode($params);
    }
}