<?php

namespace Oney\ThreeByFour\Block\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Oney\ThreeByFour\Api\Marketing\SimulationInterface;
use Oney\ThreeByFour\Block\AbstractOney;
use Oney\ThreeByFour\Helper\Config;

class Simulation extends AbstractOney
{
    /**
     * @var Session
     */
    protected $_checkoutSession;
    /**
     * @var SimulationInterface
     */
    protected $_simulationOney;
    /**
     * @var Data
     */
    protected $pricingHelper;
    /**
     * @var mixed
     */
    private $useTin;
    /**
     * @var mixed
     */
    private $useTaeg;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Simulation constructor.
     *
     * @param Template\Context                         $context
     * @param Config                                   $helperConfig
     * @param SimulationInterface                      $simulation
     * @param Session                                  $session
     * @param array                                    $data
     */
    public function __construct(
        Template\Context $context,
        Config $helperConfig,
        SimulationInterface $simulation,
        Session $session,
        Data $pricingHelper,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    )
    {
        parent::__construct($context, $helperConfig, $data);
        $this->_checkoutSession = $session;
        $this->_simulationOney = $simulation;
        $this->useTin = $helperConfig->getCountrySpecificationsConfigValue('use_tin');
        $this->checkAndSetTemplate('checkout/simulation.phtml');
        $this->pricingHelper = $pricingHelper;
        $this->priceCurrency = $priceCurrency;
    }

    public function getSimulations(){
        return $this->_simulationOney
            ->build($this->_checkoutSession->getQuote()->getGrandTotal())
            ->getSimulations();
    }

    /**
     * @param float $value
     *
     * @return mixed
     */
    public function currency($value) {
        return $this->pricingHelper->currency($value, false, false);
    }

    /**
     * @param float $value
     *
     * @return mixed
     */
    public function format($value) {
        return $this->priceCurrency->format($value);
    }

    /**
     * Get country class for specific Css use
     *
     * @return string|null
     */
    public function getCssClassCountry()
    {
        return strtolower($this->helperConfig->getConfigValue('general/country/default'));
    }

    /**
     * Use TIN ?
     *
     * @return boolean
     */
    public function useTin() {
        return $this->useTin;
    }

    /**
     * Use TIN ?
     *
     * @return boolean
     */
    public function useTaeg() {
        return $this->helperConfig->getCountrySpecificationsConfigValue("use_taeg");
    }

    public function isFree() {
        $isFree = true;
        foreach ($this->getSimulations() as $simulation) {
            if (isset($simulation['total_cost'])) {
                $isFree = $isFree && $simulation['total_cost'] == 0;
            }
        }
        return $isFree;
    }

    public function printFirstLineInLegend() {
        return (bool)$this->helperConfig->getCountrySpecificationsConfigValue('simulation_legend');
    }

    public function getInstalmentText() {
        $instalments = [];
        foreach ($this->getSimulations() as $simulation) {
            if(!in_array(count($simulation['instalments']) + 1, $instalments, true)){
                $instalments[] = count($simulation['instalments']) + 1;
            }
        }
        return implode(" ".__('or')." ", $instalments);
    }

    public function getAddLegalOnSimulation()
    {
        return $this->helperConfig->getCountrySpecificationsConfigValue('add_legal_on_simulation');
    }
}
