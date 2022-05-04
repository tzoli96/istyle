<?php

namespace Oander\AppleServices\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Oander\AppleServices\Helper\Config;

class AbstractAppleServices extends Template implements BlockInterface
{
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var array|mixed|null
     */
    protected $widgetType = null;

    /**
     * Service constructor.
     *
     * @param Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config  $config,
        array   $data = []
    )
    {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->widgetType = $this->getData('widget_type');
    }

    protected $_template = 'widget/apple_services_abstract.phtml';


    /**
     * @return string
     */
    public function getRequestButtonLabel(): string
    {
        return (string)$this->getData('request_button_label');
    }

    /**
     * @return string
     */
    public function getRedeemButtonLabel(): string
    {
        return (string)$this->getData('redeem_button_label');
    }

    /**
     * @return string
     */
    public function getCodeDescription(): string
    {
        return (string)$this->getData('code_description');
    }

    /**
     * @return string
     */
    public function getReferralToken(): string
    {
        return (string)$this->getData('referral_token');
    }


    /**
     * @return int
     */
    public function getCookieLifetime(): int
    {
        $response = false;
        switch ($this->widgetType) {
            case 'music':
                $response = $this->config->getMusicCookieLifetime();
                break;
            case 'tv':
                $response = $this->config->getTvCookieLifetime();
                break;
            case 'icloud':
                $response = $this->config->getIcloudCookieLifetime();
                break;
            case 'arcade':
                $response = $this->config->getArcadeCookieLifetime();
                break;
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getLocalStorageKey(): string
    {
        return $this->widgetType;
    }


    /**
     * @return string
     */
    public function getCaptchaKey(): string
    {
        return $this->config->getGooglRecaptchaSecretKey();
    }

    /**
     * @return string
     */
    public function getApiEndpoint(): string
    {
        $response = false;
        switch ($this->widgetType) {
            case 'music':
                $response = $this->config->getMusicEndpoint();
                break;
            case 'tv':
                $response = $this->config->getTvEndpoint();
                break;
            case 'icloud':
                $response = $this->config->getIcloudEndpoint();
                break;
            case 'arcade':
                $response = $this->config->getArcadeEndpoint();
                break;
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        $response = false;
        switch ($this->widgetType) {
            case 'music':
                $response = $this->config->getMusicSecretKey();
                break;
            case 'tv':
                $response = $this->config->getTvSecretKey();
                break;
            case 'icloud':
                $response = $this->config->getIcloudSecretKey();
                break;
            case 'arcade':
                $response = $this->config->getArcadeSecretKey();
                break;
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        $response = false;
        switch ($this->widgetType) {
            case 'music':
                $response = $this->config->getMusicUniqueId();
                break;
            case 'tv':
                $response = $this->config->getTvUniqueId();
                break;
            case 'icloud':
                $response = $this->config->getIcloudUniqueId();
                break;
            case 'arcade':
                $response = $this->config->getArcadeUniqueId();
                break;
        }
        return $response;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml();
    }

}