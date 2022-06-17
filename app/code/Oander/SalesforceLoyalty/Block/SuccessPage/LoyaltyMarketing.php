<?php

namespace Oander\SalesforceLoyalty\Block\SuccessPage;

use Magento\Framework\View\Element\Template;
use Oander\SalesforceLoyalty\Helper\Config as ConfigHelper;
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
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @param Template\Context $context
     * @param Data $helper
     * @param Session $checkoutSession
     * @param ConfigHelper $configHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data             $helper,
        Session          $checkoutSession,
        ConfigHelper     $configHelper,
        array            $data = []
    )
    {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
    }

    /**
     * @return int
     */
    public function getEarnableLoyaltyPoints(): int
    {
        return $this->helper->getEarnableLoyaltyPoints($this->checkoutSession->getLastRealOrder());
    }

    public function toHtml()
    {
        if($this->configHelper->getLoyaltyServiceEnabled())
            return parent::toHtml();
        else
            return '';
    }
}