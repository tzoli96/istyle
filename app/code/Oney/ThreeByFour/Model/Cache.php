<?php

namespace Oney\ThreeByFour\Model;

use Oney\ThreeByFour\Api\CacheInterface as OneyCache;
use Magento\Framework\App\CacheInterface;
use Oney\ThreeByFour\Logger\Logger;

class Cache implements OneyCache
{
    const LIFETIME = 86400;
    /**
     * @var CacheInterface
     */
    protected $cacheManager;
    /**
     * @var string
     */
    protected $cache = "";
    /**
     * @var string
     */
    protected $oneyCountry = "";
    /**
     * @var string
     */
    protected $specs = "";
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Cache constructor.
     *
     * @param CacheInterface $cacheManager
     */
    public function __construct(
        CacheInterface $cacheManager,
        Logger $logger
    )
    {
        $this->cacheManager = $cacheManager;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function save($data)
    {
        $this->logger->info("Saved from Cache :"."oney/". $this->oneyCountry  ."/". $this->cache, $data);
        return $this->cacheManager->save(json_encode($data),
            "oney/". $this->oneyCountry ."/". $this->cache,
            ["Oney"],
            self::LIFETIME
        );
    }

    public function load()
    {
         $data = $this->cacheManager->load("oney/". $this->oneyCountry  ."/". $this->cache);
        if($data) {
            if(is_array($data)){
                $this->logger->info("Loaded from Cache :"."oney/". $this->oneyCountry  ."/". $this->cache, $data);
                return $data;
            }
            $this->logger->info("Loaded from Cache :"."oney/". $this->oneyCountry  ."/". $this->cache, json_decode($data, true));
            return json_decode($data, true);
        }
        return [];
    }

    public function setCache($type)
    {
        $this->cache = $type;
        return $this;
    }

    public function setOneyCountry($country)
    {
        $this->oneyCountry = $country;
        return $this;
    }
}
