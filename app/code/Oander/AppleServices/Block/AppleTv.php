<?php

namespace Oander\AppleServices\Block;

use Magento\Framework\View\Element\Template\Context;
use Oander\AppleServices\Helper\Config;

class AppleTv extends AbstractAppleServices
{
    public function __construct(
        Context $context,
        Config  $config,
        array   $data = []
    )
    {
        parent::__construct($context, $config, $data);
    }

    protected $_template = 'widget/apple_tv.phtml';

    /**
     * @return int
     */
    public function getCookieLifetime(): int
    {
        return $this->config->getTvCookieLifetime();
    }

    /**
     * @return string
     */
    public function getCaptchaKey(): string
    {
        return $this->config->getTvCaptchaKey();
    }

    /**
     * @return string
     */
    public function getApiEndpoint(): string
    {
        return $this->config->getTvEndpoint();
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->config->getTvSecretKey();
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->config->getTvUniqueId();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->config->tvIsEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
