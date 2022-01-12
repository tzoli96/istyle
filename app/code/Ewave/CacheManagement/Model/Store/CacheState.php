<?php
namespace Ewave\CacheManagement\Model\Store;

use Ewave\CacheManagement\Helper\Data as Helper;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\State;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;

class CacheState implements StateInterface
{
    /**
     * Deployment config key
     */
    const CACHE_KEY = 'cache_types_store';

    /**
     * @var DeploymentConfig
     */
    protected $config;

    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var array
     */
    protected $statuses;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var bool
     */
    private $banAll;

    /**
     * Constructor
     *
     * @param DeploymentConfig $config
     * @param Writer $writer
     * @param State $state
     * @param Helper $helper
     * @param bool $banAll
     */
    public function __construct(
        DeploymentConfig $config,
        Writer $writer,
        State $state,
        Helper $helper,
        $banAll = false
    ) {
        $this->config = $config;
        $this->writer = $writer;
        $this->state = $state;
        $this->helper = $helper;
        $this->banAll = $banAll;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled($cacheType, $storeId = null)
    {
        if (null === $storeId) {
            return $this->state->isEnabled($cacheType);
        }

        $this->load();
        $storeCode = $this->helper->getStoreCode($storeId);
        return isset($this->statuses[$cacheType][$storeCode])
            ? (bool)$this->statuses[$cacheType][$storeCode]
            : false;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($cacheType, $isEnabled, $storeId = null)
    {
        if (null === $storeId) {
            $this->state->setEnabled($cacheType, $isEnabled);
        } else {
            $this->load();
            $storeCode = $this->helper->getStoreCode($storeId);
            $this->statuses[$cacheType][$storeCode] = (int)$isEnabled;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function persist($isStoreCache = false)
    {
        if (!$isStoreCache) {
            $this->state->persist();
        } else {
            $this->load();
            $this->writer->saveConfig([ConfigFilePool::APP_ENV => [self::CACHE_KEY => $this->statuses]]);
        }
    }

    /**
     * Load statuses (enabled/disabled) of cache types
     *
     * @return void
     */
    private function load()
    {
        if (null === $this->statuses) {
            $this->statuses = [];
            if ($this->banAll) {
                return;
            }
            $this->statuses = $this->config->getConfigData(self::CACHE_KEY) ?: [];
        }
    }
}
