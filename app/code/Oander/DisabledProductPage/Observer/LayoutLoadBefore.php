<?php
/**
 * Oander_DisabledProductPage
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\DisabledProductPage\Observer;

use Magento\Framework\Event\Observer;
use Oander\DisabledProductPage\Enum\Product as ProductEnum;
use Oander\DisabledProductPage\Logger\Logger;
use Oander\DisabledProductPage\Helper\Product as ProductHelper;

/**
 * Class LayoutLoadBefore
 * @package Oander\DisabledProductPage\Observer
 */
class LayoutLoadBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ProductHelper $productHelper
     * @param Logger $logger
     */
    public function __construct(
        ProductHelper $productHelper,
        Logger $logger
    ) {
        $this->productHelper = $productHelper;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        try {
            if ($this->productHelper->isDisabledProductPage()) {
                $layout = $observer->getLayout();
                if ($layout) {
                    $layout->getUpdate()->addHandle(ProductEnum::DISABLED_PRODUCT_PAGE_LAYOUT);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $this;
    }
}