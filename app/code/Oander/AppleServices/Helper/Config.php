<?php

namespace Oander\AppleServices\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Oander\AppleServices\Enum\Config as ConfigEnum;

/**
 * Class Config
 * @package Oander\AppleMusic\Helper
 */
class Config extends AbstractHelper
{
    /**
     * @var array
     */
    protected $music;

    /**
     * @var array
     */
    protected $tv;

    /**
     * @var array
     */
    protected $arcade;

    /**
     * @var array
     */
    protected $icloud;
    /**
     * @var array
     */
    protected $general;

    /**
     * Config constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);

        $this->music = (array)$this->scopeConfig->getValue(
            ConfigEnum::MUSIC_PATH,
            ScopeInterface::SCOPE_STORE
        );
        $this->tv = (array)$this->scopeConfig->getValue(
            ConfigEnum::TV_PATH,
            ScopeInterface::SCOPE_STORE
        );
        $this->icloud = (array)$this->scopeConfig->getValue(
            ConfigEnum::ICLOUD_PATH,
            ScopeInterface::SCOPE_STORE
        );
        $this->arcade = (array)$this->scopeConfig->getValue(
            ConfigEnum::ARCADE_PATH,
            ScopeInterface::SCOPE_STORE
        );
        $this->general = (array)$this->scopeConfig->getValue(
            ConfigEnum::GENERAL_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function musicIsEnabled(): bool
    {
        return (bool)$value = $this->music[ConfigEnum::GENERAL_ENABLED] ?? false;
    }

    /**
     * @return bool
     */
    public function tvIsEnabled(): bool
    {
        return (bool)$value = $this->tv[ConfigEnum::GENERAL_ENABLED] ?? false;
    }

    /**
     * @return bool
     */
    public function arcadeIsEnabled(): bool
    {
        return (bool)$value = $this->arcade[ConfigEnum::GENERAL_ENABLED] ?? false;
    }

    /**
     * @return bool
     */
    public function icloudIsEnabled(): bool
    {
        return (bool)$value = $this->icloud[ConfigEnum::GENERAL_ENABLED] ?? false;
    }

    /**
     * @return int
     */
    public function getMusicCookieLifetime(): int
    {
        return (int)$value = $this->music[ConfigEnum::GENERAL_COOKIE_LIFETIME] ?? 0;
    }

    /**
     * @return int
     */
    public function getTvCookieLifetime(): int
    {
        return (int)$value = $this->tv[ConfigEnum::GENERAL_COOKIE_LIFETIME] ?? 0;
    }

    /**
     * @return int
     */
    public function getArcadeCookieLifetime(): int
    {
        return (int)$value = $this->arcade[ConfigEnum::GENERAL_COOKIE_LIFETIME] ?? 0;
    }

    /**
     * @return int
     */
    public function getIcloudCookieLifetime(): int
    {
        return (int)$value = $this->icloud[ConfigEnum::GENERAL_COOKIE_LIFETIME] ?? 0;
    }

    /**
     * @return string
     */
    public function getMusicCaptchaKey(): string
    {
        return (string)$value = $this->music[ConfigEnum::GENERAL_CAPTCHA_KEY] ?? '';
    }

    /**
     * @return string
     */
    public function getTvCaptchaKey(): string
    {
        return (string)$value = $this->tv[ConfigEnum::GENERAL_CAPTCHA_KEY] ?? '';
    }

    /**
     * @return string
     */
    public function getArcadeCaptchaKey(): string
    {
        return (string)$value = $this->arcade[ConfigEnum::GENERAL_CAPTCHA_KEY] ?? '';
    }

    /**
     * @return string
     */
    public function getIcloudCaptchaKey(): string
    {
        return (string)$value = $this->icloud[ConfigEnum::GENERAL_CAPTCHA_KEY] ?? '';
    }

    /**
     * @return string
     */
    public function getIcloudEndpoint(): string
    {
        return (string)$value = $this->icloud[ConfigEnum::GENERAL_ENDPOINT] ?? '';
    }

    /**
     * @return string
     */
    public function getTvEndpoint(): string
    {
        return (string)$value = $this->tv[ConfigEnum::GENERAL_ENDPOINT] ?? '';
    }

    /**
     * @return string
     */
    public function getMusicEndpoint(): string
    {
        return (string)$value = $this->music[ConfigEnum::GENERAL_ENDPOINT] ?? '';
    }

    /**
     * @return string
     */
    public function getArcadeEndpoint(): string
    {
        return (string)$value = $this->arcade[ConfigEnum::GENERAL_ENDPOINT] ?? '';
    }

    /**
     * @return string
     */
    public function getIcloudUniqueId(): string
    {
        return (string)$value = $this->icloud[ConfigEnum::GENERAL_UNIQUE_ID] ?? '';
    }

    /**
     * @return string
     */
    public function getMusicUniqueId(): string
    {
        return (string)$value = $this->music[ConfigEnum::GENERAL_UNIQUE_ID] ?? '';
    }

    /**
     * @return string
     */
    public function getTvUniqueId(): string
    {
        return (string)$value = $this->tv[ConfigEnum::GENERAL_UNIQUE_ID] ?? '';
    }

    /**
     * @return string
     */
    public function getArcadeUniqueId(): string
    {
        return (string)$value = $this->arcade[ConfigEnum::GENERAL_UNIQUE_ID] ?? '';
    }

    /**
     * @return string
     */
    public function getArcadeSecretKey(): string
    {
        return (string)$value = $this->arcade[ConfigEnum::GENERAL_SECRET_KEY] ?? '';
    }

    /**
     * @return string
     */
    public function getTvSecretKey(): string
    {
        return (string)$value = $this->tv[ConfigEnum::GENERAL_SECRET_KEY] ?? '';
    }

    /**
     * @return string
     */
    public function getMusicSecretKey(): string
    {
        return (string)$value = $this->music[ConfigEnum::GENERAL_SECRET_KEY] ?? '';
    }

    /**
     * @return string
     */
    public function getIcloudSecretKey(): string
    {
        return (string)$value = $this->icloud[ConfigEnum::GENERAL_SECRET_KEY] ?? '';
    }

    /**
     * @return bool
     */
    public function getIsTestMode(): bool
    {
        return (bool)$this->general[ConfigEnum::GENERAL_TEST_MODE];
    }

    /**
     * @return string
     */
    public function getGooglRecaptchaSecretKey(): string
    {
        return '6LcfdaUZAAAAAAnXLk41_m_fbGVSR3pCWr1MsK-S';
        return (string)$this->scopeConfig->getValue(
                'googlerecaptcha/general/invisible/api_secret',
                ScopeInterface::SCOPE_STORE
            ) ?? '';
    }


    /**
     * @param string $path
     * @param string $storeCode
     *
     * @return mixed
     */
    public function getValue(string $path, string $storeCode = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
}
