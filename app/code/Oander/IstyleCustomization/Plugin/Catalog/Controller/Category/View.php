<?php

namespace Oander\IstyleCustomization\Plugin\Catalog\Controller\Category;


class View
{
    protected $scopeConfig;
    protected $algoliaConfigHelper;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Algolia\AlgoliaSearch\Helper\ConfigHelper $algoliaConfigHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->algoliaConfigHelper = $algoliaConfigHelper;
        $this->storeManager = $storeManager;
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

        $remove = $this->algoliaConfigHelper->replaceCategories($this->storeManager->getStore()->getId());

        if ($remove) {
            $layout = $page->getLayout();
            foreach ($removedLayouts as $removedLayout) {
                $layout->unsetElement($removedLayout);
            }
        }

        return $page;
    }
}