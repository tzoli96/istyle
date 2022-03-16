<?php

namespace Oander\IstyleCustomization\Rewrite\Amasty\Payrestriction\Xnotif\Plugin\ConfigurableProduct;

use Amasty\Xnotif\Plugins\ConfigurableProduct\Data as OriginalData;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Framework\Module\Manager;
use Amasty\Xnotif\Helper\Data as DataHelper;
use Magento\Framework\Registry;

class Data extends OriginalData
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var DataHelper
     */
    private $helper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Image $imageHelper
     * @param DataHelper $helper
     * @param Registry $registry
     * @param Manager $moduleManager
     */
    public function __construct(
        Image      $imageHelper,
        DataHelper $helper,
        Registry   $registry,
        Manager    $moduleManager
    ){
        $this->imageHelper = $imageHelper;
        $this->moduleManager = $moduleManager;
        $this->helper = $helper;
        $this->registry = $registry;
        parent::__construct($imageHelper, $helper, $registry, $moduleManager);
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        $aStockStatus = [];
        $allowAttributes = $this->getAllowAttributes($currentProduct);

        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            $images = $this->getGalleryImages($product);
            if ($images) {
                foreach ($images as $image) {
                    $options['images'][$productId][] =
                        [
                            'thumb' => $image->getData('small_image_url'),
                            'img' => $image->getData('medium_image_url'),
                            'full' => $image->getData('large_image_url'),
                            'caption' => $image->getLabel(),
                            'position' => $image->getPosition(),
                            'isMain' => $image->getFile() == $product->getImage(),
                        ];
                }
            }

            $key = [];
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());

                $options[$productAttributeId][$attributeValue][] = $productId;
                $options['index'][$productId][$productAttributeId] = $attributeValue;

                /*Amasty code start - code here for improving performance*/
                $key[] = $attributeValue;
            }

            if ($key && !$this->moduleManager->isEnabled('Amasty_Stockstatus')) {
                $saleable = $this->helper->isItemSalable($product);

                $aStockStatus[implode(',', $key)] = [
                    'is_in_stock' => $saleable,
                    'custom_status' => (!$saleable) ? __('Out of Stock') : '',
                    'product_id' => $product->getId()
                ];
                if (!$saleable) {
                    $aStockStatus[implode(',', $key)]['stockalert'] =
                        $this->helper->getStockAlert($product);
                }

                $aStockStatus[implode(',', $key)]['pricealert'] =
                    $this->helper->getPriceAlert($product);
            }
            /*Amasty code end*/
        }
        $aStockStatus['is_in_stock'] = $this->helper->isItemSalable($currentProduct);

        $this->registry->unregister('amasty_xnotif_data');
        $this->registry->register('amasty_xnotif_data', $aStockStatus);

        return $options;
    }
}