<?php
namespace Ewave\CacheManagement\Plugin\Magento\Framework\App\Cache;

use Magento\Framework\App\Cache\TypeList as Subject;
use Ewave\CacheManagement\Model\Store\CacheTypeList;
use Ewave\CacheManagement\Helper\Data as Helper;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\Store;

class TypeListPlugin
{
    /**
     * @var CacheTypeList
     */
    protected $cacheTypeList;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $postStoreIdKeys = [];

    /**
     * TypeListPlugin constructor.
     * @param CacheTypeList $cacheTypeList
     * @param RequestInterface $request
     * @param Helper $helper
     * @param array $postStoreIdKeys
     */
    public function __construct(
        CacheTypeList $cacheTypeList,
        RequestInterface $request,
        Helper $helper,
        array $postStoreIdKeys = []
    ) {
        $this->cacheTypeList = $cacheTypeList;
        $this->request = $request;
        $this->helper = $helper;
        $this->postStoreIdKeys = $postStoreIdKeys;
    }

    /**
     * @param Subject $subject
     * @param string $typeCode
     * @return array
     */
    public function beforeInvalidate(
        Subject $subject,
        $typeCode
    ) {
        $storeIds = $this->getStoreIds();
        if (!empty($storeIds)) {
            foreach ($storeIds as $storeId) {
                $this->cacheTypeList->invalidate($typeCode, $storeId);
            }
        } else {
            foreach ($this->helper->getStores() as $store) {
                $this->cacheTypeList->invalidate($typeCode, $store->getId());
            }
            $this->cacheTypeList->invalidate($typeCode);
        }
        return [$typeCode];
    }

    /**
     * @return array
     */
    protected function getStoreIds()
    {
        $storeIds = [];
        foreach ($this->postStoreIdKeys as $storeIdKey) {
            $storeIds = $this->request->getParam($storeIdKey, []);
            if (!empty($storeIds)) {
                break;
            }
        }

        if (empty($storeIds) && $this->request->getParam('scope') == 'stores') {
            $storeIds = $this->request->getParam('scope_id');
        }

        if (!empty($storeIds)) {
            if (!is_array($storeIds)) {
                $storeIds = [$storeIds];
            }
        } else {
            $storeIds = [];
        }

        if (in_array(Store::DEFAULT_STORE_ID, $storeIds)) {
            $storeIds = [];
        }

        return $storeIds;
    }
}
