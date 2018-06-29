<?php


namespace Oander\CategoryUrlModifier\Observer\Catalog;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Store\Model\Store;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\ResourceConnection;

class CategorySaveBefore implements \Magento\Framework\Event\ObserverInterface
{
    /** @var CategoryUrlRewriteGenerator */
    protected $categoryUrlRewriteGenerator;

    /** @var UrlRewriteHandler */
    protected $urlRewriteHandler;
    /**
     * @var CategoryInterface
     */
    private $category;

    /** @var CategoryUrlPathGenerator */
    protected $categoryUrlPathGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider */
    protected $childrenCategoriesProvider;

    /** @var StoreViewService */
    protected $storeViewService;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param UrlRewriteHandler $urlRewriteHandler
     * @param CategoryInterface $category
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param StoreViewService $storeViewService
     * @param Attribute $eavAttribute
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        UrlRewriteHandler $urlRewriteHandler,
        CategoryInterface $category,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        ChildrenCategoriesProvider $childrenCategoriesProvider,
        StoreViewService $storeViewService,
        Attribute $eavAttribute,
        ResourceConnection $resourceConnection
    )
    {
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->urlRewriteHandler = $urlRewriteHandler;
        $this->category = $category;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->storeViewService = $storeViewService;
        $this->_eavAttribute = $eavAttribute;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    )
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        if ($category->dataHasChangedFor('url_key')
            || $category->dataHasChangedFor('is_anchor')
            || $category->getIsChangedProductList()
        ) {
            $categoryurlkeyattributeid = $this->_eavAttribute->getIdByCode('catalog_category','url_key');
            $categoryurlpathattributeid = $this->_eavAttribute->getIdByCode('catalog_category','url_path');
            $connection = $this->resourceConnection->getConnection();
            $errors = array();
            //milyen urleket állítana
            $urlRewrites = array_merge(
                $this->categoryUrlRewriteGenerator->generate($category),
                $this->urlRewriteHandler->generateProductUrlRewrites($category)
            );

            //összegyűtöm indexre őket
            /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite $urlRewrite */
            $rewrites = array();
            foreach ($urlRewrites as $urlRewrite) {
                $rewrites[$urlRewrite->getRequestPath() . $urlRewrite->getStoreId()][] = $urlRewrite;
            }
            //ütközéseknél kiszedem a kategória típusút
            $categoryrewrites = array();
            $productrewrites = array();
            foreach ($rewrites as $rewrite) {
                if (count($rewrite) > 1) {
                    foreach ($rewrite as $urlRewrite) {
                        if ($urlRewrite->getEntityType() == 'category') {
                            $categoryrewrites[$urlRewrite->getEntityId()] = $urlRewrite;
                        }
                        if ($urlRewrite->getEntityType() == 'product') {
                            $productrewrites[$urlRewrite->getRequestPath()][$urlRewrite->getEntityId()] = 1;
                        }
                    }
                }
            }
            foreach ($productrewrites as $productrewrite) {
                if (count($productrewrite) > 1) {
                    $errors[] = "Sorry, no changes made! We found same url product entities, please check them IDs:" . implode(',',array_keys($productrewrite));
                }
            }
            //ütközéseknél kiszedem a kategória típusút
            $categorytemps = array();
            foreach ($categoryrewrites as $categoryid => $urlRewrite) {
                /** @var \Magento\Catalog\Model\Category $categorytemp */
                $categorytemp = $this->category->load($categoryid);
                $categorytemps[$categoryid] = $categorytemp;
                //kivétel kategória itt hibát kell dobnom
                if ($categorytemp->getLevel() < 2) {
                    $errors[] = "Sorry, no changes made! We found a Laci super category which urlkey need to be changed manually! ID:" . $categoryid;
                }
            }
            if (count($errors)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(implode('<br>', $errors))
                );
            }
            foreach ($categorytemps as $categorytemp) {
                if ($categorytemp->getId() == $category->getId()) {
                    $category->setUrlKey($category->getUrlKey() . '-1');
                    $category->setUrlKey($this->categoryUrlPathGenerator->getUrlKey($category));
                    $category->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category));
                    if($categoryurlkeyattributeid)
                    {
                        $rowcount = $connection->update('catalog_category_entity_varchar', array('value' => $category->getUrlKey()), "attribute_id = {$categoryurlkeyattributeid} and entity_id = {$category->getId()}");
                        $connection->commit();
                    }
                    if($categoryurlpathattributeid)
                    {
                        $rowcount = $connection->update('catalog_category_entity_varchar', array('value' => $category->getUrlPath()), "attribute_id = {$categoryurlpathattributeid} and entity_id = {$category->getId()}");
                        $connection->commit();
                    }
                    $category->getResource()->saveAttribute($category, 'url_key');
                    $category->getResource()->saveAttribute($category, 'url_path');
                    if ($category->dataHasChangedFor('url_path')) {
                        $this->updateUrlPathForChildren($category);
                    }
                } else {
                    $categorytemp->setUrlKey($categorytemp->getUrlKey() . '-1');
                    $categorytemp->setUrlKey($this->categoryUrlPathGenerator->getUrlKey($categorytemp));
                    $categorytemp->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($categorytemp));
                    if($categoryurlkeyattributeid)
                    {
                        $connection->update('catalog_category_entity_varchar', array('value' => $categorytemp->getUrlKey()), "attribute_id = {$categoryurlkeyattributeid} and entity_id = {$categorytemp->getId()}");
                    }
                    if($categoryurlpathattributeid)
                    {
                        $connection->update('catalog_category_entity_varchar', array('value' => $categorytemp->getUrlPath()), "attribute_id = {$categoryurlpathattributeid} and entity_id = {$categorytemp->getId()}");
                    }
                    $categorytemp->getResource()->saveAttribute($categorytemp, 'url_key');
                    $categorytemp->getResource()->saveAttribute($categorytemp, 'url_path');
                    if ($categorytemp->dataHasChangedFor('url_path')) {
                        $this->updateUrlPathForChildren($categorytemp);
                    }
                }
            }
        }
    }

    /**
     * @param Category $category
     * @return void
     */
    protected function updateUrlPathForChildren(Category $category)
    {
        $children = $this->childrenCategoriesProvider->getChildren($category, true);

        if ($this->isGlobalScope($category->getStoreId())) {
            foreach ($children as $child) {
                foreach ($category->getStoreIds() as $storeId) {
                    if ($this->storeViewService->doesEntityHaveOverriddenUrlPathForStore(
                        $storeId,
                        $child->getId(),
                        Category::ENTITY
                    )) {
                        $child->setStoreId($storeId);
                        $this->updateUrlPathForCategory($child);
                    }
                }
            }
        } else {
            foreach ($children as $child) {
                $child->setStoreId($category->getStoreId());
                $this->updateUrlPathForCategory($child);
            }
        }
    }
    /**
     * Check is global scope
     *
     * @param int|null $storeId
     * @return bool
     */
    protected function isGlobalScope($storeId)
    {
        return null === $storeId || $storeId == Store::DEFAULT_STORE_ID;
    }

    /**
     * @param Category $category
     * @return void
     */
    protected function updateUrlPathForCategory(Category $category)
    {
        $category->unsUrlPath();
        $category->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category));
        $category->getResource()->saveAttribute($category, 'url_path');
    }
}
