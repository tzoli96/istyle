<?php

namespace Ewave\CacheManagement\Preferences\Magento\Framework\App\Cache\Type;

use Ewave\CacheManagement\Helper\Data as Helper;
use Ewave\CacheManagement\Model\Store\CacheState as StoreCacheState;
use Magento\Framework\App\Cache\Type\FrontendPool;

class Config extends \Magento\Framework\App\Cache\Type\Config
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
     * Config constructor.
     * @param FrontendPool $cacheFrontendPool
     * @param Helper $helper
     * @param StoreCacheState $storeCacheState
     */
    public function __construct(
        FrontendPool $cacheFrontendPool,
        Helper $helper,
        StoreCacheState $storeCacheState
    ) {
        parent::__construct($cacheFrontendPool);
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
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
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
     * {@inheritdoc}
     */
    public function test($identifier)
    {
        $identifier = $this->modifyIdentifier($identifier);
        return parent::test($identifier);
    }

    /**
     * @param string $identifier
     * @return string
     */
    protected function modifyIdentifier($identifier)
    {
        if ($this->storeCacheState->isEnabled(self::TYPE_IDENTIFIER) && ($storeTag = $this->helper->getStoreTag())) {
            $identifier .= '_' . $storeTag;
        }
        return $identifier;
    }
}
