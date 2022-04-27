<?php

namespace Oander\AppleServices\Block;

use Magento\Framework\View\Element\Template\Context;
use Oander\AppleServices\Helper\Config;

class AppleMusic extends AbstractAppleServices
{
    public function __construct(
        Context $context,
        Config  $config,
        array   $data = []
    )
    {
        parent::__construct($context, $config, $data);
    }

    protected $_template = 'widget/apple_music.phtml';

    /**
     * @return int
     */
    public function getCookieLifetime(): int
    {
        return $this->config->getMusicCookieLifetime();
    }

    /**
     * @return string
     */
    public function getCaptchaKey(): string
    {
        return $this->config->getMusicCaptchaKey();
    }

    /**
     * @return string
     */
    public function getApiEndpoint(): string
    {
        return $this->config->getMusicEndpoint();
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->config->getMusicSecretKey();
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->config->getMusicUniqueId();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->config->musicIsEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
