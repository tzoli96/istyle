<?php

namespace Oney\ThreeByFour\Block;

use Magento\Framework\View\Element\Template;
use Oney\ThreeByFour\Api\Marketing\BusinessTransactionsInterface;
use Oney\ThreeByFour\Helper\Config;

class Homepage extends AbstractOney
{

    /**
     * @var array
     */
    protected $businessTransactions;
    /**
     * @var array
     */
    protected $instalments = [];

    public function __construct(
        Template\Context $context,
        Config $helperConfig,
        BusinessTransactionsInterface $businessTransactionsApi,
        array $data = []
    )
    {
        parent::__construct($context, $helperConfig,$data);
        $this->businessTransactions = $businessTransactionsApi->getActiveBusinessTransactions();
        $this->checkAndSetTemplate('homepage.phtml');
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

    public function isFree() {
        $isFree = !empty($this->businessTransactions);
        foreach ($this->businessTransactions as $bu) {
            $isFree = $bu['without_fee'] && $isFree;
        }
        return $isFree;
    }

    public function isShown() {
        return $this->helperConfig->getConfigValue("facilypay/front/horizontal_enabled");
    }

    public function getOneyInfoUrl() {
        return Config::URL_ONEY_INFO;
    }
}
