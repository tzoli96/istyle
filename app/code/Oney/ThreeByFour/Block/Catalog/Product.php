<?php

namespace Oney\ThreeByFour\Block\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data as TaxHelper;
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
    protected $simulation;
    /**
     * @var array
     */
    protected $instalments = [];
    /**
     * @var Config
     */
    protected $configHelper;

    protected $taxHelper;

    protected $isConfigurable = false;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;


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
        TaxHelper $taxHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = [])
    {
        parent::__construct($context, $urlEncoder, $jsonEncoder, $string, $productHelper, $productTypeConfig, $localeFormat, $customerSession, $productRepository, $priceCurrency, $data);
        $this->configHelper = $configHelper;
        $this->taxHelper = $taxHelper;
        $this->pricingHelper = $pricingHelper;
        if($configHelper->getCountrySpecificationsConfigValue('country')) {
            $this->checkAndSetTemplate('catalog/product.phtml');
            if ($this->getProduct()->getTypeId() == 'configurable') {
                $this->isConfigurable = true;
                $children = $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct());;
                foreach ($children as $child) {
                    $price = $this->taxHelper->getTaxPrice($child, $this->currency($child->getFinalPrice(), false, false), true);
                    if (!isset($this->simulation[(string)$price])) {
                        $this->simulation[(string)$price] = $simulation
                            ->build($price, true)
                            ->getSimulations();
                    }
                }
            } else {
                $price = $this->taxHelper->getTaxPrice($this->getProduct(), $this->currency($this->getProduct()->getFinalPrice(), false, false), true);
                $this->simulation[$price] = $simulation
                    ->build($price)
                    ->getSimulations();
            }
        }
    }

    public function getInstalments()
    {
        if (empty($this->instalments)) {
            foreach ($this->simulation as $price => $simu) {
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

    public function isCreditIntermediary() {
        return $this->configHelper->isCreditIntermediary();
    }

    public function isLegalEnabled() {
        return $this->configHelper->isLegalEnabled();
    }

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
     * @return bool
     */
    public function getIsConfigurable()
    {
        return $this->isConfigurable;
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
