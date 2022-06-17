<?php
/**
 * Oander_WonderWidgetNews
 *
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\WonderWidgetNews\Block;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * Class BaseWidget
 *
 * @package Oander\WonderWidget\Block
 */
abstract class BaseWidget extends Template implements BlockInterface
{
    const PATH_WYSIWYG = 'wysiwyg';

    /**
     * @return string
     */
    public function getTitle()
    {
        return (string)$this->getData('title');
    }

    /**
     * @return string
     */
    public function getLabelText()
    {
        return (string)$this->getData('label_text');
    }

    /**
     * @return string
     */
    public function getLabelColor()
    {
        return (string)$this->getData('label_color');
    }

    /**
     * @return string
     */
    public function getImage()
    {
        $image = (string)$this->getData('image');

        if ($image) {
            if (substr($image, 4) != 'http') {
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
    public function getImageAlt()
    {
        return (string)$this->getData('image_alt');
    }

    /**
     * @return int
     */
    public function getVisibleFrom() : int
    {
        return strtotime($this->getData('visible_from')) ?: 0;
    }

    /**
     * @return int
     */
    public function getVisibleTo(): int
    {
        return strtotime($this->getData('visible_to') . ':59') ?: PHP_INT_MAX;
    }



}
