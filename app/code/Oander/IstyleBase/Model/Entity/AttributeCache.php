<?php
/**
 * Oander_IstyleBase
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleBase\Model\Entity;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\State;

/**
 * Class AttributeCache
 */
class AttributeCache extends \Magento\Eav\Model\Entity\AttributeCache
{
    /**
     * @var State
     */
    protected $appState;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var StateInterface
     */
    private $state;

    /**
     * @var AbstractAttribute[][]
     */
    private $attributeInstances;

    /**
     * @var bool
     */
    private $isAttributeCacheEnabled;

    /**
     * @var array
     */
    private $unsupportedTypes;

    /**
     * AttributeCache constructor.
     *
     * @param CacheInterface $cache
     * @param StateInterface $state
     * @param State          $appState
     * @param array          $unsupportedTypes
     */
    public function __construct(
        CacheInterface $cache,
        StateInterface $state,
        State $appState,
        $unsupportedTypes = []
    ) {
        $this->cache = $cache;
        $this->state = $state;
        $this->unsupportedTypes = $unsupportedTypes;
        parent::__construct($cache, $state, $unsupportedTypes);

        $this->appState = $appState;
    }

    /**
     * @return bool
     */
    private function isAttributeCacheEnabled()
    {
        if ($this->isAttributeCacheEnabled === null) {
            $this->isAttributeCacheEnabled = $this->state->isEnabled(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER);
        }
        return $this->isAttributeCacheEnabled;
    }

    /**
     * Return attributes from cache
     *
     * @param string $entityType
     * @param string $suffix
     * @return object[]
     */
    public function getAttributes($entityType, $suffix = '')
    {
        if (in_array($entityType, $this->unsupportedTypes)) {
            return false;
        }
        if (isset($this->attributeInstances[$entityType . $suffix])) {
            return $this->attributeInstances[$entityType . $suffix];
        }
        if ($this->isAttributeCacheEnabled()
            && $this->appState->getAreaCode() !== \Magento\Framework\App\Area::AREA_ADMINHTML
            && $this->appState->getAreaCode() !== \Magento\Framework\App\Area::AREA_ADMIN
        ) {
            $cacheKey = self::ATTRIBUTES_CACHE_PREFIX . $entityType . $suffix;
            $attributesData = $this->cache->load($cacheKey);
            if ($attributesData) {
                $attributes = unserialize($attributesData);
                $this->attributeInstances[$entityType . $suffix] = $attributes;
                return $attributes;
            }
        }

        return false;
    }

    /**
     * Save attributes to cache
     *
     * @param string $entityType
     * @param object[] $attributes
     * @param string $suffix
     * @return bool
     */
    public function saveAttributes($entityType, $attributes, $suffix = '')
    {
        if (in_array($entityType, $this->unsupportedTypes)) {
            return true;
        }
        $this->attributeInstances[$entityType . $suffix] = $attributes;
        if ($this->isAttributeCacheEnabled()
            && $this->appState->getAreaCode() !== \Magento\Framework\App\Area::AREA_ADMINHTML
            && $this->appState->getAreaCode() !== \Magento\Framework\App\Area::AREA_ADMIN
        ) {
            $cacheKey = self::ATTRIBUTES_CACHE_PREFIX . $entityType . $suffix;
            $attributesData = serialize($attributes);
            $this->cache->save(
                $attributesData,
                $cacheKey,
                [
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG,
                    \Magento\Framework\App\Config\ScopePool::CACHE_TAG
                ]
            );
        }
        return true;
    }

    /**
     * Clear attributes cache
     *
     * @return bool
     */
    public function clear()
    {
        unset($this->attributeInstances);
        if ($this->isAttributeCacheEnabled()) {
            $this->cache->clean(
                [
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG,
                ]
            );
        }
        return true;
    }
}
