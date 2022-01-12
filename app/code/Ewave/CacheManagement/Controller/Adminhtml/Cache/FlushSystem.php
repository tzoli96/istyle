<?php
namespace Ewave\CacheManagement\Controller\Adminhtml\Cache;

use Ewave\CacheManagement\Helper\Data as Helper;
use Ewave\CacheManagement\Model\Store\CacheTypeList as StoreCacheTypeList;
use Magento\Backend\App\Action;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\View\Result\PageFactory;

class FlushSystem extends \Magento\Backend\Controller\Adminhtml\Cache\FlushSystem
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var StoreCacheTypeList
     */
    protected $storeCacheTypeList;

    /**
     * FlushSystem constructor.
     * @param Action\Context $context
     * @param TypeListInterface $cacheTypeList
     * @param StateInterface $cacheState
     * @param Pool $cacheFrontendPool
     * @param PageFactory $resultPageFactory
     * @param Helper $helper
     * @param StoreCacheTypeList $storeCacheTypeList
     */
    public function __construct(
        Action\Context $context,
        TypeListInterface $cacheTypeList,
        StateInterface $cacheState,
        Pool $cacheFrontendPool,
        PageFactory $resultPageFactory,
        Helper $helper,
        StoreCacheTypeList $storeCacheTypeList
    ) {
        parent::__construct(
            $context,
            $cacheTypeList,
            $cacheState,
            $cacheFrontendPool,
            $resultPageFactory
        );
        $this->helper = $helper;
        $this->storeCacheTypeList = $storeCacheTypeList;
    }

    /**
     * Flush all magento cache
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($store = $this->getRequest()->getParam('store')) {
            /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
            foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [$this->helper->getStoreTag($store)]);
            }

           foreach (array_keys($this->storeCacheTypeList->getTypes()) as $type) {
                   $this->storeCacheTypeList->cleanType($type, $store);
            }

            $storeCode = $this->helper->getStoreCode($store);
            //Varnish OK
            $this->_eventManager->dispatch('adminhtml_cache_flush_system_store', ['store' => $storeCode]);

            $this->messageManager->addSuccessMessage(__(
                'The Magento cache storage has been flushed for the "%1" store.',
                $storeCode
            ));

            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('adminhtml/*', ['store' => $store]);
        }

        return parent::execute();
    }
}
