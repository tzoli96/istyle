<?php

namespace Oander\IstyleCustomization\Plugin\ConfigurableProduct\Block\Product\View\Type;

/**
 * Class Configurable
 * @package Oander\IstyleCustomization\Plugin\ConfigurableProduct\Block\Product\View\Type
 */
class Configurable
{
    /**
     * @var \Magento\Framework\Locale\Format
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * Configurable constructor.
     * @param \Magento\Framework\Locale\Format $localeFormat
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(
        \Magento\Framework\Locale\Format $localeFormat,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->localeFormat = $localeFormat;
        $this->jsonEncoder = $jsonEncoder;
        $this->productMetadata = $productMetadata;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        $currentProduct = $subject->getProduct();
        $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
        $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');
        $result = $this->jsonDecoder->decode($result);
        $format = $this->localeFormat;

        $result['prices']['oldPrice']['amount'] = $format->getNumber($regularPrice->getAmount()->getValue());
        $result['prices']['basePrice']['amount'] = $format->getNumber($finalPrice->getAmount()->getBaseAmount());
        $result['prices']['finalPrice']['amount'] = $format->getNumber($finalPrice->getAmount()->getValue());

        return $this->jsonEncoder->encode($result);
    }
}