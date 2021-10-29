<?php

namespace Oney\ThreeByFour\Block;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Oney\ThreeByFour\Helper\Config as HelperConfig;

class AbstractOney extends \Magento\Framework\View\Element\Template
{
    /**
     * @var HelperConfig
     */
    protected $helperConfig;

    public function __construct(
        Template\Context $context,
        HelperConfig $helperConfig,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->helperConfig = $helperConfig;
    }

    public function isLegalTextShown() {
        return $this->helperConfig->getCountrySpecificationsConfigValue("legal_banner");
    }

    public function isCreditIntermediary() {
        return $this->helperConfig->isCreditIntermediary();
    }

    public function isExclusive() {
        return $this->helperConfig->isOneyExclusive();
    }

    public function isLegalEnabled() {
        return $this->helperConfig->isLegalEnabled();
    }

    public function getCompanyName() {
        return $this->helperConfig->getCompanyName();
    }

    public function getExclusiveText() {
        return $this->isExclusive() ? __('oney_exclusive_text') : __('oney_non_exclusive_text');
    }

    /**
     *
     */
    public function getOneyPdfUrl() {
        if($this->helperConfig->getOneyPdf()){
            return $this->getUrl('', ['_type' => UrlInterface::URL_TYPE_MEDIA]) . $this->helperConfig->getOneyPdf();
        }
        return null;
    }

    public function getOneyUrl() {
        return HelperConfig::URL_ONEY_INFO;
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
     * @param $template
     * Check if custom country template exist.
     */
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

    public function isOnlyInstalmentsFromApi() {
        return $this->helperConfig->getCountrySpecificationsConfigValue("add_an_instalment");
    }

}
