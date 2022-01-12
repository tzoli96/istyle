<?php

namespace Ewave\CacheManagement\Preferences\Magento\Framework\App\Cache;

use Ewave\CacheManagement\Helper\Data as Helper;
use Ewave\CacheManagement\Model\Store\CacheState as StoreCacheState;
use Magento\Framework\ObjectManagerInterface;

class Proxy extends \Magento\Framework\App\Cache\Proxy
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var StoreCacheState
     */
    protected $storeCacheState;

    /**
     * Proxy constructor.
     * @param ObjectManagerInterface $objectManager
     * @param Helper $helper
     * @param StoreCacheState $storeCacheState
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Helper $helper,
        StoreCacheState $storeCacheState
    ) {
        parent::__construct($objectManager);
        $this->helper = $helper;
        $this->storeCacheState = $storeCacheState;
    }

    /**
     * {@inheritdoc}
     */
    public function load($identifier)
    {
        $identifier = $this->modifyIdentifier($identifier);
        return parent::load($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save($data, $identifier, $tags = [], $lifeTime = null)
    {
        if ($storeTag = $this->helper->getStoreTag()) {
            $tags[] = $storeTag;
        }

        $identifier = $this->modifyIdentifier($identifier);
        return parent::save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        $identifier = $this->modifyIdentifier($identifier);
        return parent::remove($identifier);
    }

    /**
     * @param string $identifier
     * @return string
     */
    protected function modifyIdentifier($identifier)
    {
        if ($this->helper->isEnabled() && ($storeTag = $this->helper->getStoreTag())) {
            $identifier .= '_' . $storeTag;
        }
        return $identifier;
    }
}
