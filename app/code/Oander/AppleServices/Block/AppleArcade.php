<?php

namespace Oander\AppleServices\Block;

use Magento\Framework\View\Element\Template\Context;
use Oander\AppleServices\Helper\Config;

class AppleArcade extends AbstractAppleServices
{
    public function __construct(
        Context $context,
        Config  $config,
        array   $data = []
    )
    {
        parent::__construct($context, $config, $data);
    }

    protected $_template = 'widget/apple_arcade.phtml';

    /**
     * @return int
     */
    public function getCookieLifetime(): int
    {
        return $this->config->getArcadeCookieLifetime();
    }

    /**
     * @return string
     */
    public function getCaptchaKey(): string
    {
        return $this->config->getArcadeCaptchaKey();
    }

    /**
     * @return string
     */
    public function getApiEndpoint(): string
    {
        return $this->config->getArcadeEndpoint();
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->config->getArcadeSecretKey();
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->config->getArcadeUniqueId();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->config->arcadeIsEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
