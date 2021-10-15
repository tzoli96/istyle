<?php

namespace Oander\SalesforceLoyalty\Block\SuccessPage;

use Magento\Framework\View\Element\Template;

class LoyaltySubscribe extends Template
{
    const LOYALTY_REGISTRATION_PROCCESS_PATH = "/salesforceloyalty/customer/loyaltyregistrationprocess";

    public function __construct(
        Template\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return self::LOYALTY_REGISTRATION_PROCCESS_PATH;
    }
}