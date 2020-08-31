<?php
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
