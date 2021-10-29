<?php

namespace Oney\ThreeByFour\Model\Api;

use Exception;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Message\ManagerInterface;
use Oney\ThreeByFour\Helper\Config as HelperConfig;
use Oney\ThreeByFour\Logger\Logger;
use Magento\Framework\App\Http\Context;

abstract class ApiAbstract
{
    /**
     * @var Curl
     */
    protected $client;

    protected $api_url;

    protected $params;
    /**
     * @var HelperConfig
     */
    protected $_helperConfig;
    /**
     * @var Logger
     */
    protected $_logger;
    /**
     * @var ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var array
     */
    protected $_headers;

    /**
     * @param Curl             $client
     * @param HelperConfig     $config
     * @param Logger           $logger
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Curl $client,
        HelperConfig $config,
        Logger $logger,
        ManagerInterface $messageManager
    )
    {
        $this->client = $client;
        $this->_helperConfig = $config;
        $this->_logger = $logger;
        $this->_messageManager = $messageManager;
    }

    /**
     * @param string $method
     * @param string $url
     *
     * @return string
     * @throws Exception
     */
    protected function call($method = "GET", $url = "")
    {
        $this->addHeader('Content-Type', 'application/json');
        $this->client->setHeaders($this->_headers);
        $this->_logger->info("Oney API Request :: ");
        $this->_logger->info("===> PARAMS : ", $this->params);
        $this->_logger->info("===> HEADERS : ", $this->_headers);

        if ($method === "GET" && !empty($this->params)) {
            $url .= "?";
            $i = 0;
            foreach ($this->params as $param => $value) {
                if($param === "business_transaction_code") {
                }
                if ($i++ !== 0) {
                    $url .= '&';
                }
                $url .= $param . "=" . $value;
            }
            $this->client->get($url);
        } elseif ($method === "POST") {
            $this->client->post($url, json_encode($this->params));
        }
        if ($this->client->getStatus() !== 200) {
            $this->_logger->info("Oney API Response :: ");
            $this->_logger->info("===> BODY : ", json_decode($this->client->getBody(), true));
            throw new Exception($this->client->getStatus());
        }
        $this->_logger->info("Oney API Response :: ");
        $this->_logger->info("===> BODY : ", json_decode($this->client->getBody(), true));
        $this->_logger->info();
        return $this->client->getBody();
    }

    protected function setHeaders($headers)
    {
        $this->_headers = $headers;
        return $this;
    }

    protected function addHeader($name, $value)
    {
        $this->_headers[$name] = $value;
        return $this;
    }

    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    public function addParam($param, $value)
    {
        $this->params[$param] = $value;
        return $this;
    }
}
