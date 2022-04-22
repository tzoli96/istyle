<?php

namespace Oander\AdinaPlayer\Plugin;

use Magento\Catalog\Helper\Product;
use Magento\Catalog\Helper\Product\View;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Design;
use Magento\Catalog\Model\Session;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page as ResultPage;
use Oander\AdinaPlayer\Helper\Data;

class ProductViewHelper extends View
{
    /**
     * @var Data
     */
    protected $adinaHelper;

    /**
     * @param Context $context
     * @param Session $catalogSession
     * @param Design $catalogDesign
     * @param Product $catalogProduct
     * @param Registry $coreRegistry
     * @param ManagerInterface $messageManager
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param Data $adinaHelper
     * @param array $messageGroups
     */
    public function __construct(
        Context                  $context,
        Session                  $catalogSession,
        Design                   $catalogDesign,
        Product                  $catalogProduct,
        Registry                 $coreRegistry,
        ManagerInterface         $messageManager,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        Data                     $adinaHelper,
        array                    $messageGroups = []
    )
    {
        $this->adinaHelper = $adinaHelper;
        parent::__construct($context, $catalogSession, $catalogDesign, $catalogProduct, $coreRegistry, $messageManager, $categoryUrlPathGenerator, $messageGroups);
    }

    /**
     * @param ResultPage $resultPage
     * @param $product
     * @param $params
     * @return $this|ProductViewHelper
     */
    public function initProductLayout(ResultPage $resultPage, $product, $params = null)
    {
        $settings = $this->_catalogDesign->getDesignSettings($product);
        $pageConfig = $resultPage->getConfig();

        if ($settings->getCustomDesign()) {
            $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
        }

        // Apply custom page layout
        if ($settings->getPageLayout()) {
            $pageConfig->setPageLayout($settings->getPageLayout());
        }

        $urlSafeSku = rawurlencode($product->getSku());

        // Load default page handles and page configurations
        if ($params && $params->getBeforeHandles()) {
            foreach ($params->getBeforeHandles() as $handle) {
                $resultPage->addPageLayoutHandles(
                    ['id' => $product->getId(), 'sku' => $urlSafeSku, 'type' => $product->getTypeId()],
                    $handle
                );
            }
        }

        $resultPage->addPageLayoutHandles(
            ['id' => $product->getId(), 'sku' => $urlSafeSku, 'type' => $product->getTypeId()]
        );

        if ($params && $params->getAfterHandles()) {
            foreach ($params->getAfterHandles() as $handle) {
                $resultPage->addPageLayoutHandles(
                    ['id' => $product->getId(), 'sku' => $urlSafeSku, 'type' => $product->getTypeId()],
                    $handle
                );
            }
        }

        // Apply custom layout update once layout is loaded
        $update = $resultPage->getLayout()->getUpdate();
        $layoutUpdates = $settings->getLayoutUpdates();
        if ($layoutUpdates) {
            if (is_array($layoutUpdates)) {
                foreach ($layoutUpdates as $layoutUpdate) {
                    $update->addUpdate($layoutUpdate);
                }
            }
        }

        $currentCategory = $this->_coreRegistry->registry('current_category');
        $controllerClass = $this->_request->getFullActionName();
        if ($controllerClass != 'catalog-product-view') {
            $pageConfig->addBodyClass('catalog-product-view');
        }
        $pageConfig->addBodyClass('product-' . $product->getUrlKey());
        if ($currentCategory instanceof Category) {
            $pageConfig->addBodyClass('categorypath-' . $this->categoryUrlPathGenerator->getUrlPath($currentCategory))
                ->addBodyClass('category-' . $currentCategory->getUrlKey());
        }
        //Adina extended
        $resultPage = $this->adinaHelper->prepareResult($product->getData('description'), $resultPage);
        return $this;
    }
}