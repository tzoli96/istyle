<?php

namespace Oney\ThreeByFour\Block\Catalog;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Oney\ThreeByFour\Api\Marketing\BusinessTransactionsInterface;
use Oney\ThreeByFour\Block\AbstractOney;
use Oney\ThreeByFour\Helper\Config;

class Category extends AbstractOney
{
    /**
     * @var array
     */
    protected $businessTransactions;
    /**
     * @var array
     */
    protected $instalments;

    public function __construct(
        Template\Context $context,
        Config $helperConfig,
        BusinessTransactionsInterface $businessTransactionsApi,
        array $data = []
    )
    {
        parent::__construct($context, $helperConfig, $data);
        $this->businessTransactions = $businessTransactionsApi->getActiveBusinessTransactions();
        $this->checkAndSetTemplate('catalog/category.phtml');

    }

    public function getInstalments() {
        if (empty($this->instalments)) {
            foreach ($this->businessTransactions as $bu) {
                if (!isset($this->instalments[$bu['number_of_instalments']])) {
                    $this->instalments[$bu['number_of_instalments']] = $bu;
                }
            }
        }
        return $this->instalments;
    }
    /**
     * @return bool
     */
    public function isShown() {
        return (bool)$this->helperConfig->getConfigValue("facilypay/front/vertical_enabled");
    }
}
