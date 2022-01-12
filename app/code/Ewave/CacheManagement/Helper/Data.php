<?php
namespace Ewave\CacheManagement\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;

class Data extends AbstractHelper
{
    const STORE_TAG_PREFIX = 'store_';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var string
     */
    private $currentStoreCode;

    /**
     * @var int
     */
    private static $storeCodeCounter = 0;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        HttpContext $httpContext
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->httpContext = $httpContext;
    }

    /**
     * Is Cache Management enabled on the Store View level
     *
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * @param string|int|null $storeId
     * @return string|null
     */
    public function getStoreCode($storeId = null)
    {
        if (self::$storeCodeCounter++ > 0 && null === $this->currentStoreCode) {
            return null;
        }

        try {
            $this->currentStoreCode = $this->storeManager->getStore($storeId)->getCode();
        } catch (\Throwable $e) {
            $this->currentStoreCode = $this->httpContext->getValue(StoreManagerInterface::CONTEXT_STORE);
            if (null === $this->currentStoreCode) {
                self::$storeCodeCounter = 0;
            }
        }

        return $this->currentStoreCode;
    }

    /**
     * @param string|int|null $storeId
     * @return string|null
     */
    public function getStoreTag($storeId = null)
    {
        if ($storeCode = $this->getStoreCode($storeId)) {
            return self::STORE_TAG_PREFIX . $storeCode;
        }
        return null;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    public function getStores()
    {
        return $this->storeManager->getStores();
    }

    /**
     * @return array
     */
    public function getStoresCodes()
    {
        $codes = [];
        $stores = $this->getStores();
        foreach ($stores as $store) {
            $codes[] = $store->getCode();
        }
        return $codes;
    }
}
