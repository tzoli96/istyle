<?php
/**
 * Oander_WonderWidgetNews
 *
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\WonderWidgetNews\Block;

/**
 * Class Basic
 *
 * @package Oander\WonderWidgetNews\Block
 */
class Basic extends BaseWidget
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return (string)$this->getData('description');
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return (string)$this->getData('link');
    }

    /**
     * @return string
     */
    public function getAdditionalImage()
    {
        $image = (string)$this->getData('additional_image');

        if ($image) {
            if (substr($image,0,4) !== 'http') {
                return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                    . self::PATH_WYSIWYG . $image;
            }

            return $image;

        }

        return '';
    }

    /**
     * @return string
     */
    public function getMobileImage()
    {
        $image = (string)$this->getData('mobile_image');

        if ($image) {
            if (substr($image,0, 4) != 'http') {
                return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                    . self::PATH_WYSIWYG . $image;
            } else {
                return $image;
            }
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getButtonText()
    {
        return (string)$this->getData('button_text');
    }

    /**
     * @return string
     */
    public function getButtonColor()
    {
        return (string)$this->getData('button_color');
    }
}
