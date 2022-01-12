<?php

namespace Ewave\CacheManagement\Preferences\Magento\Framework\App\Cache\Type;

use Ewave\CacheManagement\Helper\Data as Helper;
use Ewave\CacheManagement\Model\Store\CacheState as StoreCacheState;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\App\Cache\StateInterface;

class AccessProxy extends \Magento\Framework\App\Cache\Type\AccessProxy
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
     * @var string
     */
    private $_identifier;

    /**
     * AccessProxy constructor.
     * @param FrontendInterface $frontend
     * @param StateInterface $cacheState
     * @param string $identifier
     * @param Helper $helper
     * @param StoreCacheState $storeCacheState
     */
    public function __construct(
        FrontendInterface $frontend,
        StateInterface $cacheState,
        $identifier,
        Helper $helper,
        StoreCacheState $storeCacheState
    ) {
        parent::__construct(
            $frontend,
            $cacheState,
            $identifier
        );
        $this->_identifier = $identifier;
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
     * {@inheritdoc}
     */
    protected function _isEnabled()
    {
        $isEnabled = parent::_isEnabled();
        if ($this->helper->isEnabled()) {
            $currentStoreCode = $this->helper->getStoreCode();
            if ($isEnabled && $currentStoreCode) {
                $isEnabled = $this->storeCacheState->isEnabled($this->_identifier, $currentStoreCode);
            }
        }
        return $isEnabled;
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
