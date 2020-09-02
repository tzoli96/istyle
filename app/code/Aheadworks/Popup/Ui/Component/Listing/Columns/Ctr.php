<?php
namespace Aheadworks\Popup\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Ctr
 * @package Aheadworks\Popup\Ui\Component\Listing\Columns
 */
class Ctr extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Filter Manager
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filterManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Filter\FilterManager $filterManager,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_filterManager = $filterManager;
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as &$item) {
            $item['ctr'] = $this->_getCtr($item['ctr']);
        }

        return $dataSource;
    }

    /**
     * Get CTR value
     *
     * @param float $ctr
     * @return string
     */
    protected function _getCtr($ctr)
    {
        return round($ctr, 0) . '%';
    }
}
