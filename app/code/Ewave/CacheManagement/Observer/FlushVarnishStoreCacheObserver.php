<?php
namespace Ewave\CacheManagement\Observer;

use Ewave\CacheManagement\Helper\Data as Helper;
use Magento\CacheInvalidate\Model\PurgeCache;
use Magento\PageCache\Model\Config;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class FlushVarnishStoreCacheObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\CacheInvalidate\Model\PurgeCache
     */
    protected $purgeCache;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\CacheInvalidate\Model\PurgeCache $purgeCache
     * @param Helper $helper
     */
    public function __construct(
        Config $config,
        PurgeCache $purgeCache,
        Helper $helper
    ) {
        $this->config = $config;
        $this->purgeCache = $purgeCache;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->config->getType() == Config::VARNISH && $this->config->isEnabled()) {
            $store = $observer->getEvent()->getStore();
            if ($storeTag = $this->helper->getStoreTag($store)) {
                $this->purgeCache->sendPurgeRequest($storeTag);
            }
        }
    }
}
