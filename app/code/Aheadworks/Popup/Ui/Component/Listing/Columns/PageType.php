<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Popup\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class PageType
 * @package Aheadworks\Popup\Ui\Component\Listing\Columns
 */
class PageType extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
            $item['page_type'] = $this->_prepareContent($item['page_type']);
        }
        return $dataSource;
    }

    /**
     * Prepare content
     *
     * @param string $pageTypes
     * @return string
     */
    protected function _prepareContent($pageTypes)
    {
        $content = [];
        $pageTypes = explode(',', $pageTypes);
        if (!is_array($pageTypes)) {
            $pageTypes = [$pageTypes];
        }
        $pageSource = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Popup\Model\Source\PageType::class);
        foreach ($pageTypes as $pageType) {
            $label = $pageSource->getLabelByValue($pageType);
            if (null === $label) {
                continue;
            }
            $content[] = $label;
        }

        return implode(', ', $content);
    }
}
