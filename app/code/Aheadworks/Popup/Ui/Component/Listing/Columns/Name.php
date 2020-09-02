<?php
namespace Aheadworks\Popup\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Name
 * @package Aheadworks\Popup\Ui\Component\Listing\Columns
 */
class Name extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Filter manager
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
            $item['name'] = $this->_getLink($item['id'], $item['name']);
        }

        return $dataSource;
    }

    /**
     * Get link for name
     *
     * @param int $entityId
     * @param string $name
     * @return string
     */
    protected function _getLink($entityId, $name)
    {
        $url = $this->context->getUrl('popup_admin/popup/edit', ['id' => $entityId]);
        return '<a href="' . $url . '" target="_blank" onclick="setLocation(this.href)">' . $name . '</a>';
    }
}
