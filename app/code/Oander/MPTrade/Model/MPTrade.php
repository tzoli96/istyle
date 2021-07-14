<?php

namespace Oander\MPTrade\Model;

use Oander\MPTrade\Api\MPTradeInterface;
use Oander\MPTrade\Helper\Data as Helper;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Webapi\Rest\Request;

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

    /**
     * @var Request
     */
    protected $request;

    protected $endpoint;

    public function __construct(
        Helper $helper,
        Curl $curlClient,
        Request $request
    ) {
        $this->helper = $helper;
        $this->endpoint = ($this->helper->getEnvironment()) ? $this->helper::LIVE_ENDPOINT_URL : $this->helper::TEST_ENDPOINT_URL;
        $this->curlClient = $curlClient;
        $this->request = $request;
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

    public function postData()
    {
        $data = $this->request->getBodyParams();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint . 'buyout');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Token '.$this->helper->getApiKey(),
                'Content-Type: application/json'
            ]
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            return false;
        } else {
           return $result;
        }
    }
}