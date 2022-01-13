<?php


namespace Oney\ThreeByFour\Model\Api\Marketing;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Message\ManagerInterface;
use Oney\ThreeByFour\Api\CacheInterface;
use Oney\ThreeByFour\Api\Marketing\BusinessTransactionsInterface;
use Oney\ThreeByFour\Api\Marketing\SimulationInterface;
use Oney\ThreeByFour\Helper\Config as HelperConfig;
use Oney\ThreeByFour\Logger\Logger;
use Oney\ThreeByFour\Model\Api\ApiAbstract;

class Simulation extends ApiAbstract implements SimulationInterface
{
    protected $paramsSimu = [];
    /**
     * @var BusinessTransactionsInterface
     */
    protected $_businessTransactions;
    /**
     * @var array
     */
    protected $simulationsFromCache = [];
    const SIMULATION = "simulation";
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Can send request
     * @var bool
     * */
    protected $sendRequest = true;

    /**
     * Simulation constructor.
     *
     * @param Curl                          $client
     * @param HelperConfig                  $config
     * @param Logger                        $logger
     * @param CacheInterface                $cache
     * @param ManagerInterface              $messageManager
     * @param BusinessTransactionsInterface $businessTransactions
     */
    public function __construct(
        Curl $client,
        HelperConfig $config,
        Logger $logger,
        CacheInterface $cache,
        ManagerInterface $messageManager,
        BusinessTransactionsInterface $businessTransactions
    )
    {
        $this->cache = $cache;
        $this->_businessTransactions = $businessTransactions;
        parent::__construct($client, $config, $logger, $messageManager);
    }

    /**
     * Get multiple Simulations
     *
     * @return array
     */
    public function getSimulations()
    {
        $simulations = [];
        if ($this->sendRequest == false) {
            return $simulations;
        }
        $this->_logger->info('FEYKS :: Test Simu :', [json_encode($this->paramsSimu)]);
        foreach ($this->paramsSimu as $param) {
            $simulations[] = $this->getSimulation($param);
        }

        return $simulations;
    }

    /**
     * Get single Simulation
     *
     * @param $params
     *
     * @return array
     */
    public function getSimulation($params = [])
    {
        if (!isset($params['payment_amount'], $params['business_transaction_code'])
        ) {
            return [];
        }

        //CHECK IN CACHE IF EXISTS
        $cacheIdentificator = $params['business_transaction_code'] ."_".$params['payment_amount'];
        if ($this->loadFromCache($cacheIdentificator)) {
            return $this->loadFromCache($cacheIdentificator);
        }

        $this->setParams($params);
        $this->addParam('merchant_guid', $this->_helperConfig->getGeneralConfigValue('merchant_guid'));

        $this->setHeaders([
            'X-Oney-Authorization' => $this->_helperConfig->getApiConfigValue('api_marketing'),
            'X-Oney-Partner-Country-Code' => $this->_helperConfig->getCountrySpecificationsConfigValue('country')
        ]);
        try {
            $this->_logger->info('SIMULATION API URL : '.$this->_helperConfig->getUrlForStep('simulation'));
            $simulation = json_decode($this->call('GET', $this->_helperConfig->getUrlForStep('simulation')), true);
            $this->saveDataInCache($cacheIdentificator, $simulation);

        } catch (\Exception $e) {
            return [];
        }
        return $simulation;
    }

    /**
     * Build Simulation Params for amount
     *
     * @param float $amount
     *
     * @return Simulation
     */
    public function build($amount, $force = false)
    {
        $this->_logger->info("Oney API Simulation Build for " . $amount . " :: ");
        $this->sendRequest = false;

        if ($force) {
            $this->paramsSimu = [];
        }

        if (empty($this->paramsSimu)) {
            foreach ($this->_businessTransactions->getActiveBusinessTransactions() as $bu) {

                if ($bu['min_order_total'] <= $amount &&
                    $bu['max_order_total'] >= $amount
                ) {
                    $this->paramsSimu[] = [
                        "payment_amount" => round($amount,2),
                        "business_transaction_code" => $bu['oney_code']
                    ];
                }
            }
        }
        if (!empty($this->paramsSimu)) {
            $this->sendRequest = true;
        }
        $this->_logger->info("====> PARAMS :", $this->paramsSimu);
        return $this;
    }

    /**
     * @param string $cache
     *
     * @return mixed
     */
    protected function loadFromCache($cache)
    {
        $this->cache->setCache("simulation_".$cache);
        $this->cache->setOneyCountry($this->_helperConfig->getCountrySpecificationsConfigValue("country"));
        return $this->cache->load();
    }

    /**
     * @param string $cache
     * @param array $data
     *
     * @return mixed
     */
    protected function saveDataInCache($cache, $data)
    {
        $this->cache->setCache("simulation_".$cache);
        $this->cache->setOneyCountry($this->_helperConfig->getCountrySpecificationsConfigValue("country"));
        return $this->cache->save($data);
    }
}

