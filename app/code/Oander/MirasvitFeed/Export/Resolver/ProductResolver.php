<?php
/**
 *   /$$$$$$   /$$$$$$  /$$   /$$ /$$$$$$$  /$$$$$$$$ /$$$$$$$
 *  /$$__  $$ /$$__  $$| $$$ | $$| $$__  $$| $$_____/| $$__  $$
 * | $$  \ $$| $$  \ $$| $$$$| $$| $$  \ $$| $$      | $$  \ $$
 * | $$  | $$| $$$$$$$$| $$ $$ $$| $$  | $$| $$$$$   | $$$$$$$/
 * | $$  | $$| $$__  $$| $$  $$$$| $$  | $$| $$__/   | $$__  $$
 * | $$  | $$| $$  | $$| $$\  $$$| $$  | $$| $$      | $$  \ $$
 * |  $$$$$$/| $$  | $$| $$ \  $$| $$$$$$$/| $$$$$$$$| $$  | $$
 *  \______/ |__/  |__/|__/  \__/|_______/ |________/|__/  |__/
 *
 * Oander_IstyleBase
 *
 * @author  János Pinczés <janos.pinczes@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */
declare(strict_types=1);

namespace Oander\MirasvitFeed\Export\Resolver;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Relation as ProductRelation;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ProductMetadataInterface as ProductMetadata;
use Magento\Framework\App\ResourceConnection;
use Mirasvit\Feed\Export\Context;
use Magento\Swatches\Helper\Data as SwatchesHelper;
use Mirasvit\Feed\Model\Dynamic\Attribute;
use Oander\WarehouseManager\Enum\ProductStock\StockStatus;
use Oander\WarehouseManager\Helper\ProductStockDisplay;

class ProductResolver extends \Mirasvit\Feed\Export\Resolver\ProductResolver
{
    /**
     * @var ProductRelation
     */
    private $productRelation;

    /**
     * @var ProductMetadata
     */
    private $productMetadata;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ProductStockDisplay
     */
    private $productStockDisplay;

    /**
     * ProductResolver constructor.
     * @param ProductStockDisplay $productStockDisplay
     * @param StockRegistryInterface $stockRegistry
     * @param ProductRelation $productRelation
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param ProductFactory $productFactory
     * @param ProductMetadata $productMetadata
     * @param ResourceConnection $resource
     * @param SwatchesHelper $swatchesHelper
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Attribute $dynamicAttribute
     * @param array $resolvers
     */
    public function __construct(
        ProductStockDisplay $productStockDisplay,
        StockRegistryInterface $stockRegistry,
        ProductRelation $productRelation,
        AttributeCollectionFactory $attributeCollectionFactory,
        ProductFactory $productFactory,
        ProductMetadata $productMetadata,
        ResourceConnection $resource,
        SwatchesHelper $swatchesHelper,
        Context $context,
        ObjectManagerInterface $objectManager,
        Attribute $dynamicAttribute,
        array $resolvers = []
    ) {
        parent::__construct(
            $stockRegistry, $productRelation, $attributeCollectionFactory, $productFactory,
            $productMetadata, $resource, $swatchesHelper, $context, $objectManager, $dynamicAttribute, $resolvers);
        $this->productRelation = $productRelation;
        $this->productMetadata = $productMetadata;
        $this->resource = $resource;
        $this->productFactory = $productFactory;
        $this->productStockDisplay = $productStockDisplay;
    }

    /**
     * Return full url for product
     *
     * @param Product $product
     *
     * @return string
     */
    public function getRealurl($product)
    {
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            $url = $product->getProductUrl();
            /** @var Product $parentproduct */
            $parentproduct = $this->getParent($product);
            if ($parentproduct) {
                if ($parentproduct->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    $params = array();
                    /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configtype */
                    $configtype = $parentproduct->getTypeInstance();
                    $configattributes = $configtype->getConfigurableAttributes($parentproduct);
                    foreach ($configattributes as $attribute) {
                        if (!is_null($attribute->getProductAttribute())) {
                            $code = $attribute->getProductAttribute()->getAttributeCode();
                            $params[$code] = $product->getData($code);
                        }
                    }
                    $url = $parentproduct->getProductUrl() . '?' . http_build_query($params);
                }
            }
        } else {
            $url = $product->getProductUrl();
        }

        return $url;
    }

    /**
     * Parent product model or current product
     *
     * @param Product $product
     * @return Product
     */
    public function getParent($product)
    {
        $select = $this->productRelation->getConnection()->select()->from(
            $this->productRelation->getMainTable(),
            ['parent_id']
        )->where(
            'child_id = ?',
            $product->getId()
        );
        $parentIds = $this->productRelation->getConnection()->fetchCol($select);
        if (count($parentIds)) {
            /** @var  $store */
            $websiteid = $this->getFeed()->getStore()->getWebsiteId();
            $parentIdsstring = implode(',', $parentIds);
            $select = $this->resource->getConnection()->select()->from(
                $this->resource->getTableName('catalog_product_website'),
                ['product_id']
            )->where(
                "product_id IN ({$parentIdsstring}) and website_id = {$websiteid}"
            );
            $websiteparentIds = $this->productRelation->getConnection()->fetchCol($select);
            if (count($websiteparentIds)) {
                //Sort PaerntsId
                $sortedParentIds=array_reverse($websiteparentIds,true);
                foreach ($sortedParentIds as $parentId){
                    $parentData=$this->productFactory->create()->load($parentId);
                    if($parentData->getStatus() == 1){
                        return $parentData;
                    }
                }
            } else {
                return $product;
            }
        } else {
            return $product;
        }
    }

    /**
     * Return product QTY
     *
     * @param Product $product
     * @return int
     */
    public function getQty($product)
    {
        $websiteId = $this->getFeed()->getStore()->getWebsiteId();
        $productStock = $this->productStockDisplay->getProductStock((int)$product->getId(), (int)$websiteId);

        return (int)$productStock->getWebsiteQty();
    }

    /**
     * Return product stock status
     *
     * @param Product $product
     * @return int
     */
    public function getIsInStock($product)
    {
        $websiteId = $this->getFeed()->getStore()->getWebsiteId();
        $productStock = $this->productStockDisplay->getProductStock((int)$product->getId(), (int)$websiteId);

        return $productStock->getStockStatus() === StockStatus::IN_STOCK;
    }
}