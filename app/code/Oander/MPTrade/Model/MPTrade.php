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

    protected $endponint;

    public function __construct(
        Helper $helper,
        Curl $curlClient
    ){
        $this->helper = $helper;
        $this->endponint = ($this->helper->getEnvironment()) ? $this->helper::LIVE_ENDPOINT_URL : $this->helper::TEST_ENDPOINT_URL;
        $this->curlClient = $curlClient;
    }

    public function getData($param)
    {
        $this->curlClient->addHeader("Authorization","Bearer ".$this->helper->getApiKey());
        $this->curlClient->get($this->endponint.$param);

        var_dump($this->curlClient);

        die();
        return $this->curlClient->getBody();
        // TODO: Implement getData() method.
    }

}