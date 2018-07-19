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
 *                            ,-~.
 *                          :  .o \
 *                          `.   _/`.
 *                            `.  `. `.
 *                              `.  ` .`.
 *                                `.  ``.`.
 *                        _._.-. -._`.  `.``.
 *                    _.'            .`.  `. `.
 *                 _.'            )     \   '
 *               .'             _.          "
 *             .'.-.'._     _.-'            "
 *           ;'       _'-.-'              "
 *          ; _._.-.-;  `.,,_;  ,..,,,.:"
 *         %-'      `._.-'   \_/   :;;
 *                           | |
 *                           : :
 *                           | |
 *                           { }
 *                            \|
 *                            ||
 *                            ||
 *                            ||
 *                          _ ;; _
 *                         "-' ` -"
 *
 * Oander_IstyleBase
 *
 * @author  Gabor Kuti <gabor.kuti@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\IstyleBase\Amasty\Promo\Plugin\Block\Product;

/**
 * Class Simple
 *
 * @package Oander\IstyleBase\Amasty\Promo\Plugin\Block\Product
 */
class Simple extends \Amasty\Promo\Plugin\Block\Product\Simple
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * Simple constructor.
     *
     * @param \Magento\Framework\Json\EncoderInterface        $jsonEncoder
     * @param \Magento\Framework\Locale\FormatInterface       $localeFormat
     * @param \Magento\Framework\Event\ManagerInterface       $eventManager
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->localeFormat = $localeFormat;
        $this->eventManager = $eventManager;
        $this->productMetadata = $productMetadata;
    }
    /**
     * @param \Magento\Catalog\Block\Product\View $subject
     * @param \Closure $proceed
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetJsonConfig(
        \Magento\Catalog\Block\Product\View $subject,
        \Closure $proceed
    ) {
        if (version_compare($this->productMetadata->getVersion(), '2.2.0', '<')) {
            /** @var $product \Magento\Catalog\Model\Product */
            $product = $subject->getProduct();

            $tierPrices = [];
            $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
            foreach ($tierPricesList as $tierPrice) {
                $tierPrices[] = $tierPrice['price']->getValue();
            }
            $oldPrice = $product->getPriceInfo()->getPrice('old_price')->getAmount()->getValue();
            $config = [
                'productId' => $product->getId(),
                'priceFormat' => $this->localeFormat->getPriceFormat(),
                'prices' => [
                    'oldPrice' => [
                        'amount' => $oldPrice ?: $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(),
                        'adjustments' => []
                    ],
                    'basePrice' => [
                        'amount' => $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount(),
                        'adjustments' => []
                    ],
                    'finalPrice' => [
                        'amount' => $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(),
                        'adjustments' => []
                    ]
                ],
                'idSuffix' => '_clone',
                'tierPrices' => $tierPrices
            ];

            $responseObject = new \Magento\Framework\DataObject();
            $this->eventManager->dispatch('catalog_product_view_config', ['response_object' => $responseObject]);
            if (is_array($responseObject->getAdditionalOptions())) {
                foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                    $config[$option] = $value;
                }
            }

            return $this->jsonEncoder->encode($config);
        }

        return $proceed();
    }
}