<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */


declare(strict_types = 1);

namespace Oander\IstyleCustomization\Plugin\Catalog\Helper;

use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Attributes
 * @package Oander\IstyleCustomization\Plugin\Catalog\Helper
 */
class Data
{
    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;
    /**
     * @var CollectionFactory
     */
    private $_collectionFactory;

    /**
     * Data constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory     $collectionFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory
    ) {

        $this->_storeManager      = $storeManager;
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
            /** @var Collection $categoryCollection */
            $categoryCollection = $product->getCategoryCollection();
            $categoryCollection
                ->addFieldToFilter(\Magento\Catalog\Model\Category::KEY_IS_ACTIVE, 1)
                ->addPathFilter(sprintf('^%d/%d', 1, $rootcategoryId));

            $allCategoriesByPaths  = [];
            $categoriesByPath      = $categoryCollection->getColumnValues(\Magento\Catalog\Model\Category::KEY_PATH);


            //Find only the deepest pathes
            $clearPathes = [];
            foreach ($categoriesByPath as $categoryByPath) {
                $hasit = false;
                foreach ($categoriesByPath as $categoryByPath2) {
                    if ((strpos($categoryByPath2, $categoryByPath . '/') === 0) && $categoryByPath != $categoryByPath2) {
                        $hasit = true;
                    }
                }
                if(!$hasit)
                    $clearPathes[] = $categoryByPath;
            }

            $pathwhatyouneedreturn = [];
            /** @var Category $category */
            foreach ($categoryCollection as $category) {
                $allCategoriesByPaths = array_merge($allCategoriesByPaths, $this->getCategoryPathsBySubCategoryPath($category->getPath()));
            }
            $allCategoriesByPaths  = array_unique($allCategoriesByPaths);
            $allCategoryCollection = $this->_collectionFactory->create();
            $allCategoryCollection
                ->addAttributeToSelect(\Magento\Catalog\Model\Category::KEY_NAME)
                ->addFieldToSelect(\Magento\Catalog\Model\Category::KEY_PATH)
                ->addFieldToSelect(\Magento\Catalog\Model\Category::KEY_LEVEL)
                ->addFieldToFilter(\Magento\Catalog\Model\Category::KEY_LEVEL, ['gt' => 0])
                ->addFieldToFilter(\Magento\Catalog\Model\Category::KEY_PATH, ['in' => $allCategoriesByPaths])
                ->setOrder(\Magento\Catalog\Model\Category::KEY_LEVEL, 'ASC')
                ->setOrder(\Magento\Catalog\Model\Category::KEY_POSITION, 'ASC');

            $pathwhatyouneed = ['1', $rootcategoryId];
            $level           = 2;
            /** @var Category $category */
            foreach ($allCategoryCollection as $category) {
                $pathwhatyouneedstring = implode('/', $pathwhatyouneed);
                if (in_array($pathwhatyouneedstring, $clearPathes)) {
                    break;
                } else {
                    if (substr($category->getPath(), 0, strlen($pathwhatyouneedstring)) === $pathwhatyouneedstring && $level == $category->getLevel()) {
                        $pathwhatyouneedreturn['category' . $category->getId()] = [
                            'label' => $category->getName(),
                            'link' => $category->getUrl()
                        ];
                        $level ++;
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
        $categorypaths    = [];
        $lastcategorypath = '';
        $categoryids      = explode('/', $subCategoryPath);
        foreach ($categoryids as $categoryid) {
            $lastcategorypath = $lastcategorypath . $categoryid;
            $categorypaths[]  = $lastcategorypath;
            $lastcategorypath = $lastcategorypath . '/';
        }

        return $categorypaths;
    }
}
