<?php


namespace Oney\ThreeByFour\Model\Api\Marketing;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Message\ManagerInterface;
use Oney\ThreeByFour\Api\CacheInterface;
use Oney\ThreeByFour\Api\Marketing\BusinessTransactionsInterface;
use Oney\ThreeByFour\Helper\Config as HelperConfig;
use Oney\ThreeByFour\Logger\Logger;
use Oney\ThreeByFour\Model\Api\ApiAbstract;
use Oney\ThreeByFour\Model\Method\Facilypay;

class BusinessTransactions extends ApiAbstract implements BusinessTransactionsInterface
{
    /**
     * @var CacheInterface
     */
    protected $cache;
    /**
     * @var mixed
     */
    private $response;
    /**
     * @var mixed
     */
    private $businessTransactions;

    public function __construct(
        Curl $client,
        HelperConfig $config,
        Logger $logger,
        ManagerInterface $messageManager,
        CacheInterface $cache
    )
    {
        parent::__construct($client, $config, $logger, $messageManager);
        $this->businessTransactions = $this->loadFromCache($config, $cache);
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function getActiveBusinessTransactions($store = null) {
        $activeTransactions = [];
        foreach ($this->getBusinessTransactions($store) as $bu) {
            if($this->_helperConfig->isPaymentActiveForCode($bu['code'])) {
                $activeTransactions[$bu['code']] = $bu;
            }
        }
        return $activeTransactions;
    }

    /**
     * @inheritDoc
     */
    public function getBusinessTransactions($store = null)
    {
        $this->_logger->info("Business Transaction API ");
        if(empty($this->businessTransactions) || $this->businessTransactions === false) {
            $this->setHeaders([
                'X-Oney-Authorization' => $this->_helperConfig->getApiConfigValue('api_marketing',$store),
                'X-Oney-Partner-Country-Code' => $this->_helperConfig->getCountrySpecificationsConfigValue('country',$store)
            ]);
            $this->setParams([
                'psp_guid' => $this->_helperConfig->getGeneralConfigValue('psp_guid',$store),
                'merchant_guid' => $this->_helperConfig->getGeneralConfigValue('merchant_guid',$store)
            ]);
            try {
                $this->_logger->info("Business Transaction API ");
                $this->_logger->info("Business Transaction URL : ".$this->_helperConfig->getUrlForStep('business_transactions',$store));
                $response = json_decode($this->call('GET', $this->_helperConfig->getUrlForStep('business_transactions',$store)), true);
                $this->_logger->info("Business Transaction API RESPONSE : ". json_encode($response));
                $this->businessTransactions = $this->formatMethods($response);
                $this->saveInCache();
            }catch (\Exception $e) {
                $this->_logger->info("Business Transaction API Error :" . $e->getMessage());
                $this->setResponse($e->getMessage());
                return [];
            }
        }
        array_multisort(array_column($this->businessTransactions, 'number_of_instalments'), SORT_ASC, $this->businessTransactions);
        $this->setResponse(200);
        return $this->businessTransactions;
    }

    /**
     * @param array $businessTransactions
     *
     * @return array
     */
    public function formatMethods($businessTransactions)
    {
        $methods = [];
        $add_instalment = $this->_helperConfig->getCountrySpecificationsConfigValue("add_an_instalment");
        foreach ($businessTransactions as $businessTransaction) {
            $methods["facilypay_" . $businessTransaction["business_transaction_code"]] = [
                "code" => "facilypay_" . $businessTransaction["business_transaction_code"],
                "min_order_total" => $businessTransaction["minimum_selling_price"],
                "max_order_total" => $businessTransaction["maximum_selling_price"],
                "title" => $businessTransaction["customer_label"],
                "model" => Facilypay::class,
                "number_of_instalments" => $businessTransaction['minimum_number_of_instalments']+$add_instalment,
                "oney_code" => $businessTransaction['business_transaction_code'],
                "without_fee" => $businessTransaction['free_business_transaction']
            ];
        }
        return $methods;
    }

    /**
     * @inheritDoc
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    protected function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @param HelperConfig   $config
     * @param CacheInterface $cache
     *
     * @return mixed
     */
    protected function loadFromCache(HelperConfig $config, CacheInterface $cache)
    {
        $cache->setCache("business_transactions");
        $cache->setOneyCountry($config->getCountrySpecificationsConfigValue("country"));
        return $cache->load();
    }

    /**
     * @return mixed
     */
    protected function saveInCache()
    {
        $this->cache->setCache("business_transactions");
        $this->cache->setOneyCountry($this->_helperConfig->getCountrySpecificationsConfigValue("country"));
        return $this->cache->save($this->businessTransactions);
    }
}
