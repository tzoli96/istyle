<?php
/**
 * Created by PhpStorm.
 * User: janos
 * Date: 2018.03.13.
 * Time: 11:15
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
     * ProductResolver constructor.
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
    public function __construct(StockRegistryInterface $stockRegistry, ProductRelation $productRelation, AttributeCollectionFactory $attributeCollectionFactory, ProductFactory $productFactory, ProductMetadata $productMetadata, ResourceConnection $resource, SwatchesHelper $swatchesHelper, Context $context, ObjectManagerInterface $objectManager, Attribute $dynamicAttribute, array $resolvers = [])
    {
        parent::__construct($stockRegistry, $productRelation, $attributeCollectionFactory, $productFactory, $productMetadata, $resource, $swatchesHelper, $context, $objectManager, $dynamicAttribute, $resolvers);
        $this->productRelation = $productRelation;
        $this->productMetadata = $productMetadata;
        $this->resource = $resource;
        $this->productFactory = $productFactory;
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
        if ($product->getTypeId()==\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        {
            $url = $product->getProductUrl();
            /** @var Product $parentproduct */
            $parentproduct = $this->getOnlyParent($product);
            if($parentproduct)
            {
                if($parentproduct->getTypeId()==\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    $params = array();
                    /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configtype */
                    $configtype = $parentproduct->getTypeInstance();
                    $configattributes = $configtype->getConfigurableAttributes($parentproduct);
                    foreach($configattributes as $attribute)
                    {
                        if (!is_null($attribute->getProductAttribute())) {
                            $code = $attribute->getProductAttribute()->getAttributeCode();
                            $params[$code] = $product->getData($code);
                        }
                    }
                    $url = $parentproduct->getProductUrl() . '?' . http_build_query($params);
                }
            }
        }
        else
        {
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
        $magentoEdition = $this->productMetadata->getEdition();
        $select = $this->productRelation->getConnection()->select()->from(
            $this->productRelation->getMainTable(),
            ['parent_id']
        )->where(
            'child_id = ?',
            $product->getId()
        );
        $parentIds = $this->productRelation->getConnection()->fetchCol($select);
        if (count($parentIds)) {
            if ($magentoEdition == 'Enterprise') {
                $parentRowId = $parentIds[0];
                $select = $this->productRelation->getConnection()->select()->from(
                    $this->resource->getTableName('catalog_product_entity'),
                    ['entity_id']
                )->where(
                    'row_id = ?',
                    $parentRowId
                );
                $parentIds = $this->productRelation->getConnection()->fetchCol($select);
            }
            /** @var  $store */
            $websiteid = $this->getFeed()->getStore()->getWebsiteId();
            $select = $this->resource->getConnection()->select()->from(
                $this->resource->getTableName('catalog_product_website'),
                ['product_id']
            )->where(
                "product_id IN (?) and website_id = {$websiteid}",
                implode(',',$parentIds)
            );
            $websiteparentIds = $this->productRelation->getConnection()->fetchCol($select);
            if(count($websiteparentIds))
            {
                return $this->productFactory->create()->load($websiteparentIds[0]);
            }
            else
            {
                return $product;
            }
        } else {
            return $product;
        }
    }
}