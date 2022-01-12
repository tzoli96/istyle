<?php
namespace Ewave\CacheManagement\Model\Store;

use Ewave\CacheManagement\Helper\Data as Helper;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\InstanceFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\TypeList;
use Magento\Framework\Cache\ConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;

class CacheTypeList implements TypeListInterface
{
    const INVALIDATED_TYPES = 'core_website_cache_invalidate';

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $typeList;

    /**
     * @var CacheState
     */
    protected $cacheState;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\Cache\InstanceFactory
     */
    protected $factory;

    /**
     * @var \Magento\Framework\Cache\ConfigInterface
     */
    protected $config;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param TypeList $typeList
     * @param CacheState $cacheState
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\InstanceFactory $factory
     * @param \Magento\Framework\Cache\ConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param SerializerInterface $serializer
     * @param Helper $helper
     */
    public function __construct(
        TypeList $typeList,
        CacheState $cacheState,
        CacheInterface $cache,
        InstanceFactory $factory,
        ConfigInterface $config,
        StoreManagerInterface $storeManager,
        SerializerInterface $serializer,
        Helper $helper
    ) {
        $this->typeList = $typeList;
        $this->cacheState = $cacheState;
        $this->cache = $cache;
        $this->factory = $factory;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return $this->typeList->getTypes();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeLabels()
    {
        return $this->typeList->getTypeLabels();
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidated()
    {
        $invalidatedTypes = [];
        $types = $this->_getInvalidatedTypes();
        if ($types) {
            $allTypes = $this->getTypes();
            foreach (array_keys($types) as $type) {
                foreach ($this->helper->getStores() as $store) {
                    $storeCode = $store->getCode();
                    if (isset($allTypes[$type]) && isset($types[$type][$storeCode])
                        && $this->cacheState->isEnabled($type, $storeCode)
                    ) {
                        $invalidatedTypes[$type][$storeCode] = $types[$type][$storeCode];
                    }
                }
            }
        }
        return $invalidatedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate($typeCode, $storeId = null)
    {
        $types = $this->_getInvalidatedTypes();
        if (!is_array($typeCode)) {
            $typeCode = [$typeCode];
        }
        foreach ($typeCode as $code) {
            if (null !== $storeId) {
                $types[$code][$this->helper->getStoreCode($storeId)] = 1;
            } else {
                foreach ($this->helper->getStores() as $store) {
                    $types[$code][$store->getCode()] = 1;
                }
            }
        }
        $this->_saveInvalidatedTypes($types);
    }

    /**
     * {@inheritdoc}
     */
    public function cleanType($typeCode, $storeId = null)
    {
        //Nincs benne a hu_HU ez a 3 store id ahonnan indítjuk a cachelést..
        $types = $this->_getInvalidatedTypes();
        if (null !== $storeId) {
            $storeCode = $this->helper->getStoreCode($storeId);
            $this->_getTypeInstance($typeCode)->clean(
                \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                [$this->helper->getStoreTag($storeId)]
            );
            if (isset($types[$typeCode][$storeCode])) {
                unset($types[$typeCode][$storeCode]);
            }
        } else {
            foreach ($this->storeManager->getStores() as $store) {
                $this->_getTypeInstance($typeCode)->clean(
                    \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                    [$this->helper->getStoreTag($store->getId())]
                );
            }
            if (isset($types[$typeCode])) {
                unset($types[$typeCode]);
            }
            $this->typeList->cleanType($typeCode);
        }
        if (isset($types[$typeCode]) && empty($types[$typeCode])) {
            $this->typeList->cleanType($typeCode);
            unset($types[$typeCode]);
        }

        $this->_saveInvalidatedTypes($types);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getInvalidatedTypes()
    {
        $types = $this->cache->load(self::INVALIDATED_TYPES);
        if ($types) {
            $types = $this->serializer->unserialize($types);
        } else {
            $types = [];
        }
        return $types;
    }

    /**
     * {@inheritdoc}
     */
    protected function _saveInvalidatedTypes($types)
    {
        $this->cache->save($this->serializer->serialize($types), self::INVALIDATED_TYPES);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getTypeInstance($type)
    {
        $config = $this->config->getType($type);
        if (!isset($config['instance'])) {
            return null;
        }
        return $this->factory->get($config['instance']);
    }
}
