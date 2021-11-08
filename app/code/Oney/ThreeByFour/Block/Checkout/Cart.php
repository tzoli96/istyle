<?php

namespace Oney\ThreeByFour\Block\Checkout;

use Magento\Checkout\Block\Cart\Totals;
use Magento\Checkout\Model\Session;
use Oney\ThreeByFour\Api\Marketing\SimulationInterface;
use Oney\ThreeByFour\Helper\Config;
use Magento\Sales\Model\ConfigInterface;
use Magento\Sales\Model\Config as ConfigSales;

class Cart extends Totals
{
    /**
     * @var SimulationInterface
     */
    protected $_simulationOney;
    /**
     * @var Session
     */
    protected $_checkoutSession;
    /**
     * @var Config
     */
    protected $helperConfig;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        SimulationInterface $simulation,
        ConfigInterface $salesConfig,
        ConfigSales $salesConfigOld,
        Config $helperConfig,
        array $layoutProcessors = [],
        array $data = [])
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');

        if (version_compare($productMetadata->getVersion(), '2.3.3') < 0) {
            parent::__construct($context, $customerSession, $checkoutSession, $salesConfigOld, $layoutProcessors, $data);
        } else {
            parent::__construct($context, $customerSession, $checkoutSession, $salesConfig, $layoutProcessors, $data);
        }
        $this->_simulationOney = $simulation;
        $this->helperConfig = $helperConfig;
        $this->checkAndSetTemplate('checkout/cart.phtml');
    }

    public function getSimulations(){
        return $this->_simulationOney
            ->build($this->_checkoutSession->getQuote()->getGrandTotal())
            ->getSimulations();
    }

    public function renderInstalments() {
        $array = [];
        $text = "";
        foreach ($this->getSimulations() as $simu) {
            if(isset($simu['instalments']) && !in_array(count($simu['instalments']) + 1, $array, true)){
                $array[] = count($simu['instalments']) + 1;
            }
        }
        foreach ($array as $key => $item) {
            if(end($array) === $item && count($array) !== 1){
                $text .= " ".__('Or') . " " . $item;
            }
            else{
                $text .= " ".$item;
            }
        }
        return $text;
    }

    public function isCreditIntermediary() {
        return $this->helperConfig->isCreditIntermediary();
    }

    public function isLegalEnabled() {
        return $this->helperConfig->isLegalEnabled();
    }

    protected function checkAndSetTemplate($template)
    {
        if($this->helperConfig->getCountrySpecificationsConfigValue('country')) {
            if ($this->getTemplateFile(
                $this->helperConfig->getCountrySpecificationsConfigValue('country')
                .'/'.$template)
            ) {
                $template = $this->helperConfig->getCountrySpecificationsConfigValue('country') .'/'.$template;
            }
            $this->setTemplate($template);
        }
    }

    /**
     * It's for countries that have multiple language.
     * Ex: language be_FR does not exist for M2.0.
     * So we have to make a distinction beetween FR words and FR from Belgium
     * @return string
     */
    public function addCountryCodeTranslation()
    {
        return $this->helperConfig->addCountryCodeTranslation();
    }

    public function isFree() {
        $isFree = true;
        foreach ($this->getSimulations() as $simulation) {
            $isFree = $isFree && $simulation['total_cost'] == 0;
        }
        return $isFree;
    }
}
