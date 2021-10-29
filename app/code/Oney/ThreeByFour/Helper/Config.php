<?php

namespace Oney\ThreeByFour\Helper;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\State;
use Magento\Framework\Locale\Resolver;
use Magento\Sales\Model\OrderRepository;

class Config extends AbstractHelper
{
    const XML_PATH_FACILYPAY = "facilypay/";
    const XML_PATH_LEGAL_CREDIT_INTERMEDIARY = "facilypay/legal/credit_intermediary";
    const XML_PATH_LEGAL_EXCLUSIVE = "facilypay/legal/exclusive";
    const XML_PATH_LEGAL_COMPANY_NAME = "facilypay/legal/company_name";
    const XML_PATH_LEGAL_DOCUMENT = "facilypay/legal/document";
    const URL_PATH_DOCUMENT = "oney/";
    const URL_ONEY_INFO = "https://www.oney.pt/3x-4x-oney";

    private $_prefix = [
        "FR" => "33",
        "BE" => "32",
        "IT" => "39",
        "ES" => "34",
        "RO" => "40",
        "PT" => "351"
    ];
    /**
     * @var State
     */
    protected $_state;
    /**
     * @var Resolver
     */
    protected $_locale;
    /**
     * @var OrderRepository
     */
    protected $orderRepository;
    /**
     * @var InvoiceRepository
     */
    protected $invoiceRepository;

    public function __construct(
        Context $context,
        State $state,
        Resolver $locale,
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository

    )
    {
        $this->_state = $state;
        $this->_locale = $locale;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        parent::__construct($context);
    }

    public function getPhonePrefixByCountry($country)
    {
        return $this->_prefix[$country];
    }

    public function getLanguageCode()
    {
        return strtoupper(explode("_", $this->_locale->getLocale())[0]);
    }

    public function getConfigValue($field, $store = null)
    {
        if ($this->_state != null && $this->_state->getAreaCode() === FrontNameResolver::AREA_CODE) {
            $store = 0;

            preg_match("/\/store\/\d*\//",$this->_getRequest()->getPathInfo(), $match);
            if($match) {
                $store = str_replace(['store','/'],"", $match[0]);
            }

            preg_match("/\/order_id\/\d*\//",$this->_getRequest()->getPathInfo(), $match2);
            if($match2) {
                $store = $this->orderRepository->get(str_replace(['order_id','/'],"",$match2[0]))->getStoreId();
            }

            preg_match("/\/invoice_id\/\d*\//",$this->_getRequest()->getPathInfo(), $match3);
            if($match3) {
                $store = $this->invoiceRepository->get(str_replace(['invoice_id','/'],"",$match3[0]))->getStoreId();
            }
        }
        return $this->scopeConfig->getValue($field,
            ScopeInterface::SCOPE_STORE,
            $store);
    }


    public function getUrlForStep($field = '', $store = null)
    {
        $preprod = $this->getGeneralConfigValue('environnement', $store);
        if($preprod) {
            $base = $this->getGeneralConfigValue('url_api_prod', $store);
        }
        else {
            $base = $this->getGeneralConfigValue('url_api_preprod', $store);
        }
        $route = $this->getConfigValue(self::XML_PATH_FACILYPAY . 'general/routes/' . $field, $store);
        return $base . $route;
    }

    public function getApiConfigValue($field, $store = null)
    {
        return $this->getConfigValue(self::XML_PATH_FACILYPAY . 'api/' . $field, $store);
    }

    public function getGeneralConfigValue($field, $store = null)
    {
        return $this->getConfigValue(self::XML_PATH_FACILYPAY . 'general/' . $field, $store);
    }

    public function getCarrierConfig($shippingMethod = "", $field = "", $store = null)
    {
        $configValue = $this->getConfigValue('carriers/' . $shippingMethod . "/" . $field, $store);
        if ($configValue) {
            return $configValue;
        } else {
            return 1;
        }
    }

    public function getSecret($store = null)
    {
        $secret = $this->getConfigValue(self::XML_PATH_FACILYPAY . 'api/secret', $store);
        return is_null($secret) ? "None" : $secret;
    }

    /**
     * @param string $method
     * @param null   $store
     *
     * @return bool
     */
    public function isPaymentActiveForCode($method, $store = null)
    {
        return (bool)$this->getConfigValue('payment/oney_section/' . $method, $store);
    }

    /**
     * @param string|null $getTelephone
     *
     * @return bool
     */
    public function isCorrectPhone(string $getTelephone)
    {
        preg_match(
            $this->getCountrySpecificationsConfigValue('phone'),
            $getTelephone,
            $matches
        );
        return !empty($matches);
    }

    /**
     * @param $field
     * @param $country
     */
    public function getCountrySpecificationsConfigValue($field, $store = null) {
        return $this->getConfigValue(self::XML_PATH_FACILYPAY .
            "country_specifications/" .
            $this->getConfigValue("general/country/default", $store) . "/" . $field,
        $store);
    }

    /**
     * @param null $store
     *
     * @return bool
     */
    public function isCreditIntermediary($store = null) {
        return (bool)$this->getConfigValue(self::XML_PATH_LEGAL_CREDIT_INTERMEDIARY, $store);
    }

    /**
     * @param null $store
     *
     * @return bool
     */
    public function isOneyExclusive($store = null) {
        return (bool)$this->getConfigValue(self::XML_PATH_LEGAL_EXCLUSIVE, $store);
    }

    /**
     * Get Company name from Oney config
     *
     * @param int|string $store
     *
     * @return string
     */
    public function getCompanyName($store = null) {
        return $this->getConfigValue(self::XML_PATH_LEGAL_COMPANY_NAME, $store);
    }

    /**
     * Get Oney Pdf from Oney config
     *
     * @param int|string $store
     *
     * @return string
     */
    public function getOneyPdf($store = null) {
        if($this->getConfigValue(self::XML_PATH_LEGAL_DOCUMENT, $store)) {
            return self::URL_PATH_DOCUMENT . $this->getConfigValue(self::XML_PATH_LEGAL_DOCUMENT, $store);
        }
        return null;
    }

    /**
     * @param null $store
     *
     * @return bool
     */
    public function isLegalEnabled($store = null) {
        return (bool)$this->getCountrySpecificationsConfigValue("legal_enabled", $store);
    }

    /**
     * @param null $store
     *
     * @return bool
     */
    public function hasSecure($store = null) {
        return (bool)$this->getCountrySpecificationsConfigValue("has_secure", $store);
    }

    /**
     * @param null $store
     *
     * @return string
     */
    public function addCountryCodeTranslation($store = null) {
        $country_code = '';
        if ($this->getCountrySpecificationsConfigValue("add_country_code_translation", $store)) {
            $country_code = ' _'.strtolower($this->getConfigValue("general/country/default"));
        }
        return $country_code;
    }
}
