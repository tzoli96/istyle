<?php

namespace Ewave\CacheManagement\Cron;

use Ewave\CacheManagement\Helper\Config as ConfigHelper;
use Ewave\CacheManagement\Model\Store\CacheTypeList;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class FlushInvalidatedCacheTypes
{
    const STORE_CODE_PLACEHOLDER = '{{store_code}}';
    const JOB_NAME_BEGINNING = 'ewave_cache_management_flush_cache_types_';

    const JOB_NAME_PATTERN = self::JOB_NAME_BEGINNING . self::STORE_CODE_PLACEHOLDER;

    /**
     * @var CacheTypeList
     */
    protected $cacheTypeList;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * FlushInvalidatedCacheTypes constructor.
     * @param CacheTypeList $cacheTypeList
     * @param ConfigHelper $configHelper
     * @param EventManager $eventManager
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        CacheTypeList $cacheTypeList,
        ConfigHelper $configHelper,
        EventManager $eventManager,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->cacheTypeList = $cacheTypeList;
        $this->configHelper = $configHelper;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param string $storeCode
     * @param array $arguments
     * @return void
     */
    public function __call($storeCode, $arguments = [])
    {
        try {
            $store = $this->storeManager->getStore($storeCode);
        } catch (NoSuchEntityException $e) {
            return;
        }

        try {
            if (!$this->configHelper->isFlushCacheByCronEnabled($store->getId())) {
                return;
            }

            $cacheTypes = $this->configHelper->getFlushCacheByCronCacheTypes($store->getId());
            if (empty($cacheTypes)) {
                return;
            }

            $invalidated = $this->cacheTypeList->getInvalidated();
            $invalidatedTypes = [];
            foreach ($invalidated as $type => $storeCodes) {
                if (!empty($storeCodes[$storeCode])) {
                    $invalidatedTypes[] = $type;
                }
            }
            $cacheTypesToClean = array_intersect($cacheTypes, $invalidatedTypes);

            if (empty($cacheTypesToClean)) {
                return;
            }

            foreach ($cacheTypes as $cacheType) {
                $this->cacheTypeList->cleanType($cacheType, $storeCode);
            }
            $this->eventManager->dispatch('adminhtml_cache_flush_system_store', ['store' => $storeCode]);
        } catch (\Throwable $e) {
            $this->logger->critical(
                'An error occurred during trying to clean cache for store ' . $storeCode . ': ' . $e
            );
        }
    }
}
