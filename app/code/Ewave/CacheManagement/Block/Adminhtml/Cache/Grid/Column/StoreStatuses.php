<?php
namespace Ewave\CacheManagement\Block\Adminhtml\Cache\Grid\Column;

use Ewave\CacheManagement\Model\Store\CacheTypeList;
use Ewave\CacheManagement\Model\Store\CacheState;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;

class StoreStatuses extends Column
{
    /**
     * @var CacheTypeList
     */
    protected $cacheTypeList;

    /**
     * @var CacheState
     */
    protected $cacheState;

    /**
     * @param Context $context
     * @param CacheTypeList $cacheTypeList
     * @param CacheState $websiteCacheState
     * @param array $data
     */
    public function __construct(
        Context $context,
        CacheTypeList $cacheTypeList,
        CacheState $websiteCacheState,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheState = $websiteCacheState;
    }

    /**
     * Add to column decorated status
     *
     * @return array
     */
    public function getFrameCallback()
    {
        return [$this, 'decorateStatus'];
    }

    /**
     * Decorate status column values
     *
     * @param string $value
     * @param \Magento\Framework\Model\AbstractModel $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function decorateStatus($value, $row, $column, $isExport)
    {
        $cell = '<div class="store-status">';
        $storeCodes = [];
        $invalidedTypes = $this->cacheTypeList->getInvalidated();
        $stores = $this->_storeManager->getStores();
        foreach ($stores as $store) {
            $storeCodes[] = $store->getCode();
        }

        sort($storeCodes, SORT_STRING | SORT_FLAG_CASE);
        foreach ($storeCodes as $storeCode) {
            $class = 'critical';
            if (!$this->cacheState->isEnabled($row->getId())) {
                $class = 'critical';
            } else if (isset($invalidedTypes[$row->getId()][$storeCode])) {
                $class = 'minor';
            } else if ($this->cacheState->isEnabled($row->getId(), $storeCode)) {
                $class = 'notice';
            }
            $cell .= '<span class="status grid-severity-' . $class . '"><span>'
                . $storeCode
                . '</span></span>';
        }
        return $cell.'</div>';
    }
}
