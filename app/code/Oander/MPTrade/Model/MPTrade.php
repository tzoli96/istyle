<?php

namespace Oander\MPTrade\Model;

use Oander\MPTrade\Api\MPTradeInterface;
use Oander\MPTrade\Helper\Data as Helper;
use Magento\Framework\HTTP\Client\Curl;

class MPTrade implements MPTradeInterface
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var Curl
     */
    protected $curlClient;

    protected $endpoint;

    public function __construct(
        Helper $helper,
        Curl $curlClient
    ) {
        $this->helper = $helper;
        $this->endpoint = ($this->helper->getEnvironment()) ? $this->helper::LIVE_ENDPOINT_URL : $this->helper::TEST_ENDPOINT_URL;
        $this->curlClient = $curlClient;
    }

    /**
     * @param string $param
     * @param string $param2
     * @param string $param3
     * @return mixed|string
     */
    public function getData($param, $param2 = null, $param3 = null)
    {
        $params = implode('/', array_filter([$param,$param2,$param3]));
        $this->curlClient->addHeader("Authorization","Token ".$this->helper->getApiKey());
        $this->curlClient->get($this->endpoint.$params);

        return $this->curlClient->getBody();
    }

}