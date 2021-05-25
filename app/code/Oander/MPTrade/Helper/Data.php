<?php

namespace Oander\MPTrade\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const CONFIG_PATH           = 'mp_trade/general/';
    const ENABLED_PARAM         = 'enabled';
    const API_PARAM             = 'api_key';
    const ENVIRONMENT           = 'environment';
    const API_VERSION           = '2.0';
    const MAGENTO_ENDPOINT      = 'rest/V1/api/oander/mptrade';
    const TEST_ENDPOINT_URL     = 'https://api-test.istyle-byeback.cz/api/v1/';
    const LIVE_ENDPOINT_URL     = 'https://www.istyle-byeback.cz/api/v1/';


    /**
     * @var Context
     */
    protected $context;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Resolver
     */
    protected $localeResolver;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Resolver $localeResolver
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Resolver $localeResolver
    ) {
        $this->context = $context;
        $this->storeManager = $storeManager;
        $this->localeResolver = $localeResolver;
        parent::__construct($context);
    }

    protected function getConfigValue(string $param)
    {
        return $this->context->getScopeConfig()->getValue(
            self::CONFIG_PATH . $param,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isEnabled(): bool
    {
        $value = $this->getConfigValue(self::ENABLED_PARAM);
        return (bool)$value;
    }

    public function getApiKey(): string
    {
        return $this->getConfigValue(self::API_PARAM);
    }

    public function getEnvironment(): bool
    {
        return $this->getConfigValue(self::ENVIRONMENT);
    }

    public function getApiVersion(): string
    {
        return self::API_VERSION;
    }

    /**
     * @return string
     */
    public function getEndPoint(): string
    {
        return ($this->getEnvironment()) ? self::LIVE_ENDPOINT_URL : self::TEST_ENDPOINT_URL;
    }

    /**
     * @return string
     */
    public function getMagentoEndpoint(): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB) . self::MAGENTO_ENDPOINT;
    }



}