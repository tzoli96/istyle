<?php

namespace Oander\OneyThreeByFourExtender\Block\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Catalog\Block\Product\Context;
use Oney\ThreeByFour\Api\Marketing\SimulationInterface;
use Oney\ThreeByFour\Helper\Config;

class Product extends \Magento\Catalog\Block\Product\View
{
    /**
     * @var array
     */
    protected $simulationArray;

    /**
     * @var array
     */
    protected $instalments = [];

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var \Oander\OneyThreeByFourExtender\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var SimulationInterface
     */
    protected $simulation;

    protected $taxHelper = null;

    protected $isConfigurable = false;

    /**
     * @param Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param EncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param Config $configHelper
     * @param SimulationInterface $simulation
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Oander\OneyThreeByFourExtender\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        EncoderInterface $jsonEncoder,
        StringUtils $string,
        Config $configHelper,
        SimulationInterface $simulation,
        \Magento\Catalog\Helper\Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        \Oander\OneyThreeByFourExtender\Helper\Data $helper,
        array $data = [])
    {
        parent::__construct($context, $urlEncoder, $jsonEncoder, $string, $productHelper, $productTypeConfig, $localeFormat, $customerSession, $productRepository, $priceCurrency, $data);
        $this->configHelper = $configHelper;
        $this->taxHelper = $context->getCatalogHelper();
        $this->simulation = $simulation;
        $this->helper = $helper;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getInstalments()
    {
        if (empty($this->instalments)) {
            if (empty($this->simulationArray)) {
                $this->generateSimulations();
            }
            foreach ($this->simulationArray as $price => $simu) {
                if (!empty($simu)) {
                    foreach ($simu as $bu) {
                        if (isset ($bu['instalments']) && !isset($this->instalments[count($bu['instalments']) + 1 ])) {
                            $this->instalments[count($bu['instalments'])+ 1] = $bu;
                        }
                    }
                    break;
                }
            }
        }
        return $this->instalments;
    }

    /**
     * @return bool
     */
    public function isCreditIntermediary() {
        return $this->configHelper->isCreditIntermediary();
    }

    /**
     * @return bool
     */
    public function isLegalEnabled() {
        return $this->configHelper->isLegalEnabled();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->generateSimulations();

        return parent::_toHtml();
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function generateSimulations()
    {
        if($this->configHelper->getCountrySpecificationsConfigValue('country')) {
            $this->checkAndSetTemplate('catalog/product.phtml');

            if ($this->getData('productFinalPrice')) {
                $price = $this->getData('productFinalPrice');
                $price = $this->taxHelper->getTaxPrice($this->getProduct(), $this->currency($price, false, false), true);

                $this->simulationArray[(string)$price] = $this->simulation
                    ->build($price)
                    ->getSimulations();
            }

            if ($this->getProduct()) {
                if ($this->getProduct()->getTypeId() == 'configurable') {
                    $children = $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct());
                    foreach ($children as $child) {
                        $price = $this->taxHelper->getTaxPrice($child, $this->currency($child->getFinalPrice(), false, false), true);
                        if (!isset($this->simulationArray[(string)$price])) {
                            $this->simulationArray[(string)$price] = $this->simulation
                                ->build($price, true)
                                ->getSimulations();
                        }
                    }
                } elseif ($this->getProduct()->getTypeId() == 'bundle') {
                    $price = $this->helper->getProductFinalPrice($this->getProduct());
                    $price = $this->taxHelper->getTaxPrice($this->getProduct(), $this->currency($price, false, false), true);

                    $this->simulationArray[(string)$price] = $this->simulation
                        ->build($price, true)
                        ->getSimulations();

                } else {
                    $price = $this->taxHelper->getTaxPrice($this->getProduct(), $this->currency($this->getProduct()->getFinalPrice(), false, false), true);
                    $this->simulationArray[(string)$price] = $this->simulation
                        ->build($price)
                        ->getSimulations();
                }
            }
        }
    }

    /**
     * @param $template
     * @return void
     */
    protected function checkAndSetTemplate($template)
    {
        if($this->configHelper->getCountrySpecificationsConfigValue('country')) {
            $this->setAreaCode($this->configHelper->getCountrySpecificationsConfigValue('country'));
            if ($this->getTemplateFile(
                $this->configHelper->getCountrySpecificationsConfigValue('country')
                .'/'.$template)
            ) {
                $template = $this->configHelper->getCountrySpecificationsConfigValue('country') .'/'.$template;
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
        return $this->configHelper->addCountryCodeTranslation();
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
}
