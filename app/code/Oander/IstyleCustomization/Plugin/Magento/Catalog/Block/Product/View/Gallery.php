<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Catalog\Block\Product\View;

use Magento\Catalog\Block\Product\View\Gallery as OriginalGallery;

class Gallery
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->imageHelper = $imageHelper;
    }

    /**
     * Retrieve product images in JSON format
     *
     * @return string
     */
    public function aroundGetGalleryImagesJson(
        \Magento\Catalog\Block\Product\View\Gallery $subject,
        callable $proceed
    ) {
        $imagesItems = [];
        foreach ($subject->getGalleryImages() as $image) {
            $imagesItems[] = [
                'thumb' => $image->getData('small_image_url'),
                'img' => $image->getData('medium_image_url'),
                'full' => $image->getData('large_image_url'),
                'caption' => $image->getLabel(),
                'position' => $image->getPosition(),
                'isMain' => $subject->isMainImage($image),
                'video' => $image->getData('video_url'),
            ];
        }

        if (empty($imagesItems)) {
            $imagesItems[] = [
                'thumb' => $this->imageHelper->getDefaultPlaceholderUrl('thumbnail'),
                'img' => $this->imageHelper->getDefaultPlaceholderUrl('image'),
                'full' => $this->imageHelper->getDefaultPlaceholderUrl('image'),
                'caption' => '',
                'position' => '0',
                'isMain' => true,
            ];
        }

        return json_encode($imagesItems);
    }
}
