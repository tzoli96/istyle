<?php

namespace Oander\SalesforceLoyalty\Block\SuccessPage;

use Magento\Framework\View\Element\Template;
use Oander\SalesforceLoyalty\Helper\Data;
use Magento\Checkout\Model\Session;

class LoyaltyMarketing extends Template
{
    /**
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Template\Context $context
     * @param Data $helper
     * @param Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data             $helper,
        Session          $checkoutSession,
        array            $data = []
    )
    {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function getEarnableLoyaltyPoints(): int
    {
        return $this->helper->getEarnableLoyaltyPoints($this->checkoutSession->getLastRealOrder());
    }
}