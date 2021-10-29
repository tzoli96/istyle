<?php

namespace Oney\ThreeByFour\Block\Pedagogique;

use Magento\Framework\View\Element\Template;
use Oney\ThreeByFour\Api\Marketing\BusinessTransactionsInterface;
use Oney\ThreeByFour\Block\AbstractOney;
use Oney\ThreeByFour\Helper\Config;

class Modal extends AbstractOney
{
    protected $instalments = [];
    protected $businessTransactions = [];

    public function __construct(
        Template\Context $context,
        BusinessTransactionsInterface $businessTransactionsApi,
        Config $helperConfig,
        array $data = []
    )
    {
        $this->businessTransactions = $businessTransactionsApi->getActiveBusinessTransactions();
        parent::__construct($context, $helperConfig, $data);
        $this->checkAndSetTemplate('pedagogique/modal.phtml');
    }

    /**
     * @return array
     */
    public function getBusinessTransactions()
    {
        return $this->businessTransactions;
    }

    /**
     * @return string
     */
    public function getInstalmentsText()
    {
        $instalments = [];
        foreach ($this->getInstalments() as $instalment) {
            $instalments[] = $instalment['number_of_instalments'];
        }
        sort($instalments);
        return implode(" ".__('or')." ", $instalments);
    }

    public function getInstalments()
    {
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
     * @return bool|mixed
     */
    public function isFree()
    {
        $free = true;
        foreach ($this->businessTransactions as $bu){
            $free = $free && $bu['without_fee'];
        }
        return $free;
    }

    public function isFullLogoShown() {
        return (bool)$this->helperConfig->getCountrySpecificationsConfigValue("full_logo_shown");
    }

    public function hasSecure() {
        return (bool)$this->helperConfig->hasSecure();
    }
}
