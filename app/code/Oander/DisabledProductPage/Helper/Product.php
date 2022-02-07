<?php
/**
 * Oander_DisabledProductPage
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\DisabledProductPage\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Oander\DisabledProductPage\Enum\Product as ProductEnum;
use Oander\DisabledProductPage\Helper\Config;
use Oander\DisabledProductPage\Logger\Logger;

/**
 * Class Product
 * @package Oander\DisabledProductPage\Helper
 */
class Product extends AbstractHelper
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param Config $config
     * @param Registry $registry
     * @param Logger $logger
     * @param Context $context
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Config $config,
        Registry $registry,
        Logger $logger,
        Context $context
    ) {
        parent::__construct($context);

        $this->productRepository = $productRepository;
        $this->config = $config;
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * @param $productId
     * @return bool
     */
    public function isShowDisabledProductPage($productId)
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        try {
            $product = $this->productRepository->getById($productId);
            if ($product
                && $product->getStatus() == Status::STATUS_DISABLED
                && in_array($product->getVisibility(), [
                    Visibility::VISIBILITY_BOTH,
                    Visibility::VISIBILITY_IN_CATALOG
                ])
            ) {
                if ($product->getTypeId() == Configurable::TYPE_CODE) {
                    $children = $product->getTypeInstance()->getUsedProducts($product);
                    foreach ($children as $child){
                        if ($child->getStatus() == Status::STATUS_ENABLED) {
                            return false;
                        }
                    }
                }

                return true;
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isDisabledProductPage()
    {
        if ($this->registry->registry(ProductEnum::DISABLED_PRODUCT_PAGE_REGISTRY)) {
            return true;
        }

        return false;
    }
}