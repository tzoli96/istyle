<?php
namespace Ewave\CacheManagement\Observer;

use Ewave\CacheManagement\Helper\Data as Helper;
use Ewave\CacheManagement\Model\Store\CacheTypeList;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class FlushAllStoresCacheObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $cacheFrontendPool;

    /**
     * @var CacheTypeList
     */
    protected $cacheTypeList;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Pool $cacheFrontendPool
     * @param CacheTypeList $cacheTypeList
     * @param Helper $helper
     */
    public function __construct(
        Pool $cacheFrontendPool,
        CacheTypeList $cacheTypeList,
        Helper $helper
    ) {
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->cacheTypeList = $cacheTypeList;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        foreach ($this->helper->getStores() as $store) {
            /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
            foreach ($this->cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->clean(
                    \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                    [$this->helper->getStoreTag($store->getId())]
                );
            }

            foreach (array_keys($this->cacheTypeList->getTypes()) as $type) {
                $this->cacheTypeList->cleanType($type, $store->getId());
            }
        }
    }
}
