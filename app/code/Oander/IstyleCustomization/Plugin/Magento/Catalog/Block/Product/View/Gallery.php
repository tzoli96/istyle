<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Catalog\Block\Product\View;

use Magento\Catalog\Block\Product\View\Gallery as OriginalGallery;

class Gallery
{
    /**
     *
     * @return string
     */
    public function afterGetGalleryImagesJson(
        OriginalGallery $subject,
                        $result
    ) {
        $imagesItems = json_decode($result, true);
        foreach ($imagesItems as $key => $image) {
            if (isset($image['videoUrl']) && !empty($image['videoUrl'])) {
                $imagesItems[$key]['full'] = $image['videoUrl'];
            }
        }

        return json_encode($imagesItems);
    }
}