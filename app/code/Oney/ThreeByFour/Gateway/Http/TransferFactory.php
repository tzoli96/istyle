<?php

namespace Oney\ThreeByFour\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Oney\ThreeByFour\Helper\Config;
use Oney\ThreeByFour\Logger\Logger;

class TransferFactory implements TransferFactoryInterface
{
    const PAYMENT_STEP = "purchase";
    const PAYMENT_API = "api_payment";
    const COUNTRY_API = "country";
    /**
     * @var TransferBuilder
     */
    protected $transferBuilder;
    /**
     * @var Config
     */
    protected $_helperConfig;
    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @param TransferBuilder $transferBuilder
     * @param Config          $config
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        Config $config,
        Logger $logger
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->_logger = $logger;
        $this->_helperConfig = $config;
    }

    public function create(array $request)
    {
        $this->_logger->info('Oney :: transfer headers :', $this->prepareHeaders());
        return $this->transferBuilder
            ->setBody($request)
            ->setHeaders($this->prepareHeaders())
            ->setUri($this->_helperConfig->getUrlForStep(self::PAYMENT_STEP))
            ->setMethod(\Zend_Http_Client::POST)
            ->build();
    }

    /**
     * @return array
     */
    protected function prepareHeaders()
    {
        return [
            "X-Oney-Authorization" =>$this->_helperConfig->getApiConfigValue(self::PAYMENT_API),
            "X-Oney-Partner-Country-Code" => $this->_helperConfig->getCountrySpecificationsConfigValue(self::COUNTRY_API),
            "X-Oney-Secret" => $this->_helperConfig->getSecret()
        ];
    }
}
