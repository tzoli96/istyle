<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */


declare(strict_types=1);

namespace Oander\IstyleCustomization\Plugin\Catalog\Helper;

/**
 * Class Attributes
 * @package Oander\IstyleCustomization\Plugin\Catalog\Helper
 */
class Data
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $_collectionFactory;

    /**
     * Data constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
    )
    {

        $this->_storeManager = $storeManager;
        $this->_collectionFactory = $collectionFactory;
    }
    /**
     * @param          $subject
     * @param callable $proceed
     * @param array    $excludeAttr
     *
     * @return array
     */
    public function aroundGetBreadcrumbPath(\Magento\Catalog\Helper\Data $subject, callable $proceed)
    {
        $product = $subject->getProduct();
        if ($product) {
            $rootcategoryId = $this->_storeManager->getStore()->getRootCategoryId();
            /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
            $categoryCollection = $product->getCategoryCollection();
            $categoryCollection
                ->addFieldToFilter(\Magento\Catalog\Model\Category::KEY_IS_ACTIVE, 1)
                ->addPathFilter(sprintf('^%d/%d', 1, $rootcategoryId));

            $allCategoriesByPaths = [];
            $categoriesByPath = $categoryCollection->getColumnValues(\Magento\Catalog\Model\Category::KEY_PATH);
            $pathwhatyouneedreturn = [];
            /** @var \Magento\Catalog\Model\ResourceModel\Category $category */
            foreach ($categoryCollection as $category) {
                $allCategoriesByPaths = array_merge($allCategoriesByPaths, $this->getCategoryPathsBySubCategoryPath($category->getPath()));
            }
            $allCategoriesByPaths = array_unique($allCategoriesByPaths);
            $allCategoryCollection = $this->_collectionFactory->create();
            $allCategoryCollection
                ->addAttributeToSelect(\Magento\Catalog\Model\Category::KEY_NAME)
                ->addFieldToSelect(\Magento\Catalog\Model\Category::KEY_PATH)
                ->addFieldToSelect(\Magento\Catalog\Model\Category::KEY_LEVEL)
                ->addFieldToFilter(\Magento\Catalog\Model\Category::KEY_LEVEL, array('gt' => 0))
                ->addFieldToFilter(\Magento\Catalog\Model\Category::KEY_PATH, array('in' => $allCategoriesByPaths))
                ->setOrder(\Magento\Catalog\Model\Category::KEY_LEVEL, 'ASC')
                ->setOrder(\Magento\Catalog\Model\Category::KEY_POSITION, 'ASC');

            $pathwhatyouneed = ['1', $rootcategoryId];
            $level = 2;
            /** @var \Magento\Catalog\Model\ResourceModel\Category $category */
            foreach ($allCategoryCollection as $category) {
                $pathwhatyouneedstring = implode('/', $pathwhatyouneed);
                if (in_array($pathwhatyouneedstring, $categoriesByPath)) {
                    break;
                } else {
                    if (substr($category->getPath(), 0, strlen($pathwhatyouneedstring)) === $pathwhatyouneedstring && $level == $category->getLevel()) {
                        $pathwhatyouneedreturn['category' . $category->getId()] = [
                            'label' => $category->getName(),
                            'link' => $category->getUrl()
                        ];
                        $level++;
                        $pathwhatyouneed[] = $category->getId();
                    }
                }
            }
            $pathwhatyouneedreturn['product' . $product->getId()] = [
                'label' => strlen($product->getName()) > 20 ? (substr($product->getName(), 0, 20) . '...') : $product->getName(),
                'link' => ''
            ];
            return $pathwhatyouneedreturn;
        }
        return $proceed();
    }

    public function getCategoryPathsBySubCategoryPath($subCategoryPath)
    {
        $categorypaths = [];
        $lastcategorypath = '';
        $categoryids = explode('/', $subCategoryPath);
        foreach ($categoryids as $categoryid)
        {
            $lastcategorypath = $lastcategorypath . $categoryid;
            $categorypaths[] = $lastcategorypath;
            $lastcategorypath = $lastcategorypath .'/';
        }
        return $categorypaths;
    }
}
