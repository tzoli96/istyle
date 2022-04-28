<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\SalesforceLoyalty\Plugin\Frontend\Magento\Checkout\Block\Onepage;

use Magento\Customer\Model\Session\Proxy as CustomerSessionProxy;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;
use Oander\SalesforceLoyalty\Helper\Data;

class Success
{

    /**
     * @var CustomerSessionProxy
     */
    protected $customerSession;
    /**
     * @var Data
     */
    protected $helperData;

    public function __construct(
        CustomerSessionProxy                $customerSession,
        Data                                $helperData
    )
    {
        $this->customerSession = $customerSession;
        $this->helperData = $helperData;
    }

    public function beforeToHtml(
        \Magento\Checkout\Block\Onepage\Success $subject
    ) {
        $subject->setData('customer_session', $this->customerSession);
        $subject->setData('loyalty_registering_block_id', \Oander\SalesforceLoyalty\Enum\CMSBlock::REGISTERING);
        $subject->setData('loyalty_confirmation_block_id', \Oander\SalesforceLoyalty\Enum\CMSBlock::CONFIRMATION);
        $subject->setData('customer_loyalty_status', $this->customerSession->getCustomer()->getData(CustomerAttribute::LOYALTY_STATUS) ?? 0);
        return [];
    }
}