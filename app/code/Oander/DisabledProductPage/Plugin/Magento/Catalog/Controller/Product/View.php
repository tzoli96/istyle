<?php
/**
 * Oander_DisabledProductPage
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\DisabledProductPage\Plugin\Magento\Catalog\Controller\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Oander\DisabledProductPage\Enum\Product as ProductEnum;
use Oander\DisabledProductPage\Helper\Product as ProductHelper;
use Oander\DisabledProductPage\Logger\Logger;

/**
 * Class View
 * @package Oander\DisabledProductPage\Plugin\Magento\Catalog\Controller\Product
 */
class View
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param RequestInterface $request
     * @param ProductHelper $productHelper
     * @param Registry $registry
     * @param Logger $logger
     */
    public function __construct(
        RequestInterface $request,
        ProductHelper $productHelper,
        Registry $registry,
        Logger $logger
    ) {
        $this->request = $request;
        $this->productHelper = $productHelper;
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Catalog\Controller\Product\View $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Catalog\Controller\Product\View $subject,
        \Closure $proceed
    ) {
        try {
            $productId = (int)$this->request->getParam('id');
            if ($this->productHelper->isShowDisabledProductPage($productId)) {
                $this->registry->unregister(ProductEnum::DISABLED_PRODUCT_PAGE_REGISTRY);
                $this->registry->register(ProductEnum::DISABLED_PRODUCT_PAGE_REGISTRY, true);

                return $proceed();
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $proceed();
    }
}
