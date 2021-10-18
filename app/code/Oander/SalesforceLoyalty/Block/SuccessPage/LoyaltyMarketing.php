<?php

namespace Oander\SalesforceLoyalty\Block\SuccessPage;

use Magento\Framework\View\Element\Template;
use Oander\SalesforceLoyalty\Helper\Data;

class LoyaltyMarketing extends Template
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data             $helper,
        array            $data = []
    )
    {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function getEarnableLoyaltyPoints(): int
    {
        return $this->helper->getEarnableLoyaltyPoints();
    }
}