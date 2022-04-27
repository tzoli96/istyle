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
    }

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


}