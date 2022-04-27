<?php

namespace Oander\AppleServices\Block;

use Magento\Framework\View\Element\Template\Context;
use Oander\AppleServices\Helper\Config;

class AppleIcloud extends AbstractAppleServices
{
    public function __construct(
        Context $context,
        Config  $config,
        array   $data = []
    )
    {
        parent::__construct($context, $config, $data);
    }

    protected $_template = 'widget/apple_icloud.phtml';

    /**
     * @return int
     */
    public function getCookieLifetime(): int
    {
        return $this->config->getIcloudCookieLifetime();
    }

    /**
     * @return string
     */
    public function getCaptchaKey(): string
    {
        return $this->config->getIcloudCaptchaKey();
    }

    /**
     * @return string
     */
    public function getApiEndpoint(): string
    {
        return $this->config->getIcloudEndpoint();
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->config->getIcloudSecretKey();
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->config->getIcloudUniqueId();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->config->icloudIsEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
