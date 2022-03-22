<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Swatches\Controller\Ajax;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Swatches\Helper\Data;
use Oander\IstyleCustomization\Plugin\Magento\Swatches\Helper\Data as SwatchHelper;

class Media extends \Magento\Swatches\Controller\Ajax\Media
{
    /**
     * @var SwatchHelper
     */
    private $swatchHelperOverride;

    public function __construct(
        Context $context,
        Data $swatchHelper,
        ProductFactory $productModelFactory,
        SwatchHelper $swatchHelperOverride
    ){
        $this->swatchHelperOverride = $swatchHelperOverride;
        parent::__construct($context, $swatchHelper, $productModelFactory);
    }

    /**
     * Get product media by fallback:
     * 1stly by default attribute values
     * 2ndly by getting base image from configurable product
     *
     * @return string
     */
    public function execute()
    {
        $productMedia = [];
        if ($productId = (int)$this->getRequest()->getParam('product_id')) {
            $currentConfigurable = $this->productModelFactory->create()->load($productId);
            $attributes = (array)$this->getRequest()->getParam('attributes');
            if (!empty($attributes)) {
                $product = $this->getProductVariationWithMedia($currentConfigurable, $attributes);
            }
            if ((empty($product) || (!$product->getImage() || $product->getImage() == 'no_selection'))
                && isset($currentConfigurable)
            ) {
                $product = $currentConfigurable;
            }
            $productMedia = $this->swatchHelperOverride->getProductMediaGallery($product);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($productMedia);
        return $resultJson;
    }
}