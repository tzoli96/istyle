<?php

namespace Ewave\CacheManagement\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;

class Config extends AbstractHelper
{
    const CRON_REGEXP = '/^((?:[1-9]?\d|\*)\s*(?:(?:[\/-][1-9]?\d)|(?:,[1-9]?\d)+)?\s*){5}$/';

    const SECTION = 'ewave_cachemanagement';
    const GROUP_FLUSH_CACHE_BY_CRON = 'flush_cache_by_cron';

    const ENABLED = 'enabled';
    const CRON_EXPR = 'cron_expr';
    const CACHE_TYPES = 'cache_types';

    const FLUSH_CACHE_BY_CRON_ENABLED = self::SECTION . '/' . self::GROUP_FLUSH_CACHE_BY_CRON . '/' . self::ENABLED;
    const FLUSH_CACHE_BY_CRON_CRON_EXPR = self::SECTION . '/' . self::GROUP_FLUSH_CACHE_BY_CRON . '/' . self::CRON_EXPR;
    const FLUSH_CACHE_BY_CRON_CACHE_TYPES = self::SECTION . '/' . self::GROUP_FLUSH_CACHE_BY_CRON . '/' . self::CACHE_TYPES; // @codingStandardsIgnoreLine

    /**
     * @param mixed $storeId
     * @return bool
     */
    public function isFlushCacheByCronEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::FLUSH_CACHE_BY_CRON_ENABLED,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }

    /**
     * @param mixed $storeId
     * @return string|null
     */
    public function getFlushCacheByCronCronExpr($storeId = null)
    {
        $cronExpr = $this->scopeConfig->getValue(
            self::FLUSH_CACHE_BY_CRON_CRON_EXPR,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );

        if ($cronExpr) {
            $cronExpr = trim($cronExpr);
            if (!preg_match(self::CRON_REGEXP, $cronExpr)) {
                $cronExpr = null;
            }
        }

        return $cronExpr ?: null;
    }

    /**
     * @param mixed $storeId
     * @return array
     */
    public function getFlushCacheByCronCacheTypes($storeId = null)
    {
        $cacheTypes = $this->scopeConfig->getValue(
            self::FLUSH_CACHE_BY_CRON_CACHE_TYPES,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );

        $cacheTypes = $cacheTypes ? explode(',', $cacheTypes) : [];

        return $cacheTypes;
    }
}
