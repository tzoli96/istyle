<?php


namespace Oney\ThreeByFour\Gateway\Request;


use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Oney\ThreeByFour\Helper\Config;

class MerchantDataBuilder implements BuilderInterface
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var Config
     */
    protected $_helperConfig;

    public function __construct(
        UrlInterface $urlBuilder,
        Config $config
    )
    {
        $this->_helperConfig = $config;
        $this->_urlBuilder = $urlBuilder;
    }

    public function build(array $buildSubject)
    {
        return [
            "navigation" => [
                "success_url" => $this->_urlBuilder->getUrl("checkout/onepage/success"),
                "fail_url" => $this->_urlBuilder->getUrl("checkout/onepage/failure"),
                "server_response_url" => $this->_urlBuilder->getUrl("facilypay/payment/callback")
            ],
            "psp_guid" => $this->_helperConfig->getGeneralConfigValue("psp_guid"),
            "merchant_guid" => $this->_helperConfig->getGeneralConfigValue("merchant_guid")
        ];
    }
}
