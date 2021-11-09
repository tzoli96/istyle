<?php

namespace Oney\ThreeByFour\Block\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;
use Oney\ThreeByFour\Api\Marketing\SimulationInterface;
use Oney\ThreeByFour\Helper\Config;


class Simulation extends View
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
     * @var Config
     */
    protected $helperConfig;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    protected $simulations;

    protected $simulation_content_classes;

    protected $taxHelper = null;


    public function __construct(
        Context $context,
        EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        SimulationInterface $simulation,
        Config $helperConfig,
        array $data = [])
    {
        $this->_simulationOney = $simulation;
        $this->helperConfig = $helperConfig;
        parent::__construct($context, $urlEncoder, $jsonEncoder, $string, $productHelper, $productTypeConfig, $localeFormat, $customerSession, $productRepository, $priceCurrency, $data);
        $this->checkAndSetTemplate('catalog/simulation.phtml');
        $this->pricingHelper = $pricingHelper;
        $this->taxHelper = $context->getCatalogHelper();
    }

    public function getSimulations()
    {
        if (empty($this->simulations)) {
            if ($this->getProduct()->getTypeId() == 'configurable') {
                $children = $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct());
                $attributes = $this->getProduct()->getTypeInstance()->getConfigurableAttributes($this->getProduct());
                $attributes_code = [];
                foreach ($attributes as $a) {
                    $attributes_code[] = $a->getProductAttribute()->getAttributeCode();
                }
                foreach ($children as $child) {
                    $price = $this->taxHelper->getTaxPrice($child, $this->currency($child->getFinalPrice(), false, false), true);
                    $attributes_value = [];
                    foreach ($attributes_code as $code) {
                        $attributes_value[] = $child->getData($code);
                    }
                    sort($attributes_value);
                    if (!isset($this->simulation[(string)$price])) {
                        $this->simulations[(string)$price] = $this->_simulationOney
                            ->build($price, true)
                            ->getSimulations();
                        $this->simulation_content_classes[(string)$price] = 'simulation-'.implode('-', $attributes_value);
                    } else {
                        $this->simulation_content_classes[$price] =
                            $this->simulation_content_classes[$price].' simulation-'.implode(' ', $attributes_value);
                    }
                }

            } else {
                $price = $this->taxHelper->getTaxPrice($this->getProduct(), $this->currency($this->getProduct()->getFinalPrice(), false, false), true);
                $this->simulations[$price] = $this->_simulationOney
                    ->build($price)
                    ->getSimulations();
            }
        }

        return $this->simulations;
    }

    /**
     * @param $value
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function currency($value, $format = true, $includeContainer = true)
    {
        return $this->pricingHelper->currency($value, $format, $includeContainer);
    }

    public function format($value)
    {
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
    public function useTin()
    {
        return $this->helperConfig->getCountrySpecificationsConfigValue("use_tin");
    }

    /**
     * Use TAEG ?
     *
     * @return boolean
     */
    public function useTaeg()
    {
        return $this->helperConfig->getCountrySpecificationsConfigValue("use_taeg");
    }

    public function isFree() {
        $isFree = true;
        foreach ($this->getSimulations() as $simulations) {
            foreach ($simulations as $simulation) {
                if (isset($simulation['total_cost'])) {
                    $isFree = $isFree && $simulation['total_cost'] == 0;
                }
            }
        }
        return $isFree;
    }

    public function printFirstLineInLegend() {
        return (bool)$this->helperConfig->getCountrySpecificationsConfigValue('simulation_legend');
    }

    public function getInstalmentText() {
        $instalments = [];
        foreach ($this->getSimulations() as $simulations) {
            foreach ($simulations as $simulation) {
                if (isset($simulation['instalments']) && !in_array(count($simulation['instalments']) + 1, $instalments, true)) {
                    $instalments[] = count($simulation['instalments']) + 1;
                }
            }
            break;
        }
        sort($instalments);
        return implode(" ".__('or')." ", $instalments);
    }

    public function isLegalEnabled() {
        return (bool)$this->helperConfig->isLegalEnabled();
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

    public function getAddLegalOnSimulation()
    {
        return $this->helperConfig->getCountrySpecificationsConfigValue('add_legal_on_simulation');
    }

    public function getSimulationsContentClasses()
    {
        return $this->simulation_content_classes;
    }
}
