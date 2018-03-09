<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Model\Import;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogUrlRewrite\Ui\DataProvider\Product\Form\Modifier\ProductUrlRewrite;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\UrlRewrite\Model\UrlRewrite;
use Oander\ImportM2\Helper\Config;
use Oander\ImportM2\Helper\Data;
use Oander\ImportM2\Logger\Logger;
use Oander\ImportM2\Model\ImportBase;
use Oander\ImportM2\Model\Resource\Donor\CategoryDonor;
use Oander\ImportM2\Model\Resource\Donor\UrlRewriteDonor;
use Magento\Catalog\Model\CategoryFactory;

/**
 * Class Product
 *
 * @package Oander\ImportM2\Model\Import
 */
class Category extends ImportBase
{
    /**
     * @var CategoryDonor
     */
    private $categoryDonor;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Data
     */
    private $data;

    /**
     * @var UrlRewriteFactory
     */
    private $urlRewriteFactory;
    /**
     * @var UrlRewriteDonor
     */
    private $urlRewriteDonor;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * Category constructor.
     *
     * @param Logger                      $logger
     * @param Config                      $config
     * @param Data                        $data
     * @param CategoryDonor               $categoryDonor
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductFactory              $productFactory
     * @param UrlRewriteFactory           $urlRewriteFactory
     * @param UrlRewriteDonor             $urlRewriteDonor
     */
    public function __construct(
        Logger $logger,
        Config $config,
        Data $data,
        CategoryDonor $categoryDonor,
        CategoryRepositoryInterface $categoryRepository,
        ProductFactory $productFactory,
        UrlRewriteFactory $urlRewriteFactory,
        CategoryFactory $categoryFactory,
        UrlRewriteDonor $urlRewriteDonor
    ) {
        parent::__construct($logger, $config);
        $this->categoryDonor = $categoryDonor;
        $this->categoryRepository = $categoryRepository;
        $this->productFactory = $productFactory;
        $this->data = $data;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlRewriteDonor = $urlRewriteDonor;
        $this->categoryFactory = $categoryFactory;
    }

    public function execute()
    {
        //$this->importMissingCategories();
        $this->importProductUrlRewrites();
        $this->importCategoryUrlRewrites();
        $this->importCategoryAttributes();
    }

    private function importProductUrlRewrites()
    {
        /** @var Product $productCollection */
        foreach ($this->donorStoreIds as $donorStoreId) {
            $productCollection = $this->productFactory->create();
            $productArray = $productCollection->getCollection()
                ->addStoreFilter($this->data->getCurrentStoreId($donorStoreId))
                ->addFieldToSelect('sku')
                ->addFieldToSelect('entity_id')
                ->toArray(['sku']);

            $products = [];
            foreach ($productArray as $id => $item) {
                $products[$item['sku']] = $id;
            }

            $donorUrlRewrites = $this->urlRewriteDonor->getProductUrlRewrites($donorStoreId);
            foreach ($donorUrlRewrites as $donorUrlRewrite) {
                if (array_key_exists($donorUrlRewrite['sku'], $products)) {

                    try {
                        /** @var UrlRewrite $urlRewrite */
                        $urlRewrite = $this->urlRewriteFactory->create();
                        $urlRewrite->setStoreId($this->data->getCurrentStoreId($donorStoreId))
                            ->setEntityType('product')
                            ->setRequestPath($donorUrlRewrite['request_path'])
                            ->setRedirectType($donorUrlRewrite['redirect_type'])
                            ->setDescription($donorUrlRewrite['description'])
                            ->setTargetPath($this->getCurrentNewPath($donorUrlRewrite['target_path'],
                                $products[$donorUrlRewrite['sku']]))
                            ->setEntityId($products[$donorUrlRewrite['sku']]);

                        $urlRewrite->save();
                    } catch (\Exception $exception) {
                        $this->logger->addError($exception->getMessage() . '- sku: ' . $donorUrlRewrite['sku'] . ' url: ' . $donorUrlRewrite['request_path']);
                    }
                }
            }
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function importCategoryUrlRewrites()
    {
        foreach ($this->donorStoreIds as $donorStoreId) {
            /** @var \Magento\Catalog\Model\Category $categoryCollection */
            $categoryCollection = $this->categoryFactory->create();
            $categoryCollection = $categoryCollection->getCollection();

            foreach ($categoryCollection->getItems() as $category) {
                $categoryEntity = $this->categoryRepository->get($category->getEntityId(),
                    $this->data->getCurrentStoreId($donorStoreId));
                $donorUrlRewrites = $this->urlRewriteDonor->getCategoryRewrites($donorStoreId,
                    $categoryEntity->getName());

                foreach ($donorUrlRewrites as $donorUrlRewrite) {

                    try {
                        /** @var UrlRewrite $urlRewrite */
                        $urlRewrite = $this->urlRewriteFactory->create();
                        $urlRewrite->setStoreId($this->data->getCurrentStoreId($donorStoreId))
                            ->setEntityType('product')
                            ->setRequestPath($donorUrlRewrite['request_path'])
                            ->setRedirectType($donorUrlRewrite['redirect_type'])
                            ->setDescription($donorUrlRewrite['description'])
                            ->setTargetPath($this->getCurrentNewCategoryPath($donorUrlRewrite['target_path'],
                                $category->getEntityId()))
                            ->setEntityId($category->getEntityId());

                        $urlRewrite->save();
                    } catch (\Exception $exception) {
                        $this->logger->addError($exception->getMessage() . '- s/name: ' .
                            $this->data->getCurrentStoreId($donorStoreId) . $categoryEntity->getName()
                            . ' url: ' . $donorUrlRewrite['request_path']);
                    }
                }
            }
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function importCategoryAttributes()
    {
        $nopeAttrCodes = [
            'path',
            'children_count',
            'url_key',
            'url_path'
        ];

        foreach ($this->donorStoreIds as $donorStoreId) {
            /** @var \Magento\Catalog\Model\Category $categoryCollection */
            $categoryCollection = $this->categoryFactory->create();
            $categoryCollection = $categoryCollection->getCollection();

            foreach ($categoryCollection->getItems() as $category) {
                $categoryEntity = $this->categoryRepository->get($category->getEntityId(),
                    $this->data->getCurrentStoreId($donorStoreId));
                $attributes = $categoryEntity->getCustomAttributes();
                $isSet = false;
                foreach ($attributes as $attribute) {
                    if (!in_array($attribute->getAttributeCode(), $nopeAttrCodes)) {
                        $donorAttributeValue = $this->categoryDonor->getCategoryAttribute(
                            $donorStoreId, $categoryEntity->getName(), $attribute->getAttributeCode()
                        );

                        if ($donorAttributeValue !== null && $donorAttributeValue != $attribute->getValue()) {
                            $categoryEntity->setCustomAttribute($attribute->getAttributeCode(), $donorAttributeValue);
                            $isSet = true;
                        }
                    }
                }

                if ($isSet) {
                    $this->categoryRepository->save($categoryEntity);
                }
            }
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function importMissingCategories()
    {
        foreach ($this->donorStoreIds as $donorStoreId) {
            /** @var \Magento\Catalog\Model\Category $categoryCollection */
            $categoryCollection = $this->categoryFactory->create();
            $categoryCollection = $categoryCollection->getCollection();

            $fknCurrCategoryNames = [];
            foreach ($categoryCollection->getItems() as $category) {
                $categoryEntity = $this->categoryRepository->get($category->getEntityId(),
                    $this->data->getCurrentStoreId($donorStoreId));

                $pathIds = explode('/',$categoryEntity['path']);
                foreach ($pathIds as $pathId) {
                    $categoryEntity2 = $this->categoryRepository->get($pathId,$this->data->getCurrentStoreId($donorStoreId));
                    $fknCurrCategoryNames[$category->getEntityId()] = $categoryEntity2->getName() . '/';
                }
            }

            $donorCategories = $this->categoryDonor->getMissingCategories($donorStoreId);
            $donorCategoriesNames = [];
            foreach ($donorCategories as $donorCategory) {
                $donorCategoriesNames[$donorCategory['entity_id']] = $donorCategory['value'];
            }


            foreach ($categoryCollection->getItems() as $category) {
                $categoryEntity = $this->categoryRepository->get($category->getEntityId(),
                    $this->data->getCurrentStoreId($donorStoreId));


                //$donorCategories = $this->categoryDonor->getMissingCategories($donorStoreId);

            }
        }
    }

    private function getCurrentNewPath($donorPath, $newID)
    {
        $temp = explode('product/view/id/', $donorPath);
        if (!isset($temp[1])) {
            return $donorPath;
        }

        $temp2 = explode('/', $temp[1]);
        if (!isset($temp2[1])) {
            $temp2[1] = '';
        }

        unset($temp2[0]);
        $url = $temp[0] . 'product/view/id/' . $newID;
        foreach ($temp2 as $tmp2) {
            $url .= '/' . $tmp2;
        }

        return $url;
    }

    private function getCurrentNewCategoryPath($donorPath, $newID)
    {
        $temp = explode('category/view/id/', $donorPath);
        if (!isset($temp[1])) {
            return $donorPath;
        }

        $temp2 = explode('/', $temp[1]);
        if (!isset($temp2[1])) {
            $temp2[1] = '';
        }

        unset($temp2[0]);
        $url = $temp[0] . 'category/view/id/' . $newID;
        foreach ($temp2 as $tmp2) {
            $url .= '/' . $tmp2;
        }

        return $url;
    }
}
