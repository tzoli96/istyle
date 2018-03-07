<?php
/**
 * Oander_IstyleBase
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleBase\Model\Product;

use Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator as MagentoAnchorUrlRewriteGenerator;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

/**
 * Class AnchorUrlRewriteGenerator
 *
 * @package Oander\IstyleBase\Model\Product
 */
class AnchorUrlRewriteGenerator extends MagentoAnchorUrlRewriteGenerator
{

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /**
     * AnchorUrlRewriteGenerator constructor.
     *
     * @param ProductUrlPathGenerator     $urlPathGenerator
     * @param UrlRewriteFactory           $urlRewriteFactory
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        ProductUrlPathGenerator $urlPathGenerator,
        UrlRewriteFactory $urlRewriteFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        parent::__construct($urlPathGenerator, $urlRewriteFactory, $categoryRepository);
    }

    /**
     * @param                $storeId
     * @param Product        $product
     * @param ObjectRegistry $productCategories
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generate($storeId, Product $product, ObjectRegistry $productCategories)
    {
        $urls = [];
        foreach ($productCategories->getList() as $category) {
            $anchorCategoryIds = $category->getAnchorsAbove();
            if ($anchorCategoryIds) {
                foreach ($anchorCategoryIds as $anchorCategoryId) {
                    $anchorCategory = $this->categoryRepository->get($anchorCategoryId, $storeId);
                    $urls[] = $this->urlRewriteFactory->create()
                        ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                        ->setEntityId($product->getId())
                        ->setRequestPath(
                            $this->urlPathGenerator->getUrlPathWithSuffix(
                                $product,
                                $storeId,
                                $anchorCategory
                            )
                        )
                        ->setTargetPath(
                            $this->urlPathGenerator->getCanonicalUrlPath(
                                $product,
                                $anchorCategory
                            )
                        )
                        ->setStoreId($storeId)
                        ->setMetadata(['category_id' => $anchorCategory->getId()]);
                }
            }
        }

        return $urls;
    }
}
