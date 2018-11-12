<?php

namespace Oander\IstyleCustomization\Plugin\Catalog\Controller\Category;


class View
{
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Catalog\Controller\Category\View $subject
     * @param \Magento\Framework\View\Result\Page $page
     * @return mixed
     */
    public function afterExecute(\Magento\Catalog\Controller\Category\View $subject, $page)
    {
        $removedLayouts = [
            'category.products.list',
            'div.sidebar.main'
        ];

        $remove = (bool)$this->scopeConfig->getValue('algoliasearch_instant/instant/replace_categories', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($remove) {
            $layout = $page->getLayout();
            foreach ($removedLayouts as $removedLayout) {
                //$block = $layout->getBlock($removedLayout);
                $layout->unsetElement($removedLayout);
            }
        }

        return $page;
    }
}