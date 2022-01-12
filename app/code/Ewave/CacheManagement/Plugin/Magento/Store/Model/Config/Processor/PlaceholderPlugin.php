<?php

namespace Ewave\CacheManagement\Plugin\Magento\Store\Model\Config\Processor;

use Ewave\CacheManagement\Cron\FlushInvalidatedCacheTypes;
use Ewave\CacheManagement\Helper\Config as ConfigHelper;
use Magento\Framework\App\DeploymentConfig;
use Magento\Store\Model\Config\Processor\Placeholder;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class PlaceholderPlugin
{
    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * DefaultScope constructor.
     * @param DeploymentConfig $deploymentConfig
     * @param StoreManagerInterface $storeManager
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        StoreManagerInterface $storeManager,
        ConfigHelper $configHelper
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
    }

    /**
     * @param Placeholder $placeholder
     * @param $result
     * @return mixed
     */
    public function afterProcess(Placeholder $placeholder, $result)
    {
        if ($this->deploymentConfig->isDbAvailable()) {
            foreach ($this->storeManager->getStores() as $store) {
                $settings = $result[ScopeInterface::SCOPE_STORES][$store->getId()][ConfigHelper::SECTION]
                    [ConfigHelper::GROUP_FLUSH_CACHE_BY_CRON] ?? [];
                $enabled = $settings[ConfigHelper::ENABLED] ?? null;
                $cronExpr = $settings[ConfigHelper::CRON_EXPR] ?? null;
                $cacheTypes = $settings[ConfigHelper::CACHE_TYPES] ?? null;

                if (!$enabled || !$cronExpr || !$cacheTypes) {
                    continue;
                }

                if (!preg_match(ConfigHelper::CRON_REGEXP, $cronExpr)) {
                    continue;
                }

                $jobName = str_replace(
                    FlushInvalidatedCacheTypes::STORE_CODE_PLACEHOLDER,
                    $store->getCode(),
                    FlushInvalidatedCacheTypes::JOB_NAME_PATTERN
                );

                $result['default']['crontab']['default']['jobs'][$jobName] = [
                    'name' => $jobName,
                    'instance' => FlushInvalidatedCacheTypes::class,
                    'method' => $store->getCode(),
                    'schedule' => $cronExpr,
                ];
            }
        }

        return $result;
    }
}
