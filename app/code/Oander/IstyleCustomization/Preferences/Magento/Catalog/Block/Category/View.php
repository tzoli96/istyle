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
 * Oander_IstyleCustomization
 *
 * @author  Janos Pinczes <janos.pinczes.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Preferences\Magento\Catalog\Block\Category;

/**
 * Class View
 * @package Magento\Catalog\Block\Category
 */
class View extends \Magento\Catalog\Block\Category\View
{
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $title = $this->getCurrentCategory()->getMetaTitle();
        if ($title) {
            $this->pageConfig->getTitle()->set($title);
        }
        else
        {
            $this->pageConfig->getTitle()->set($this->getCurrentCategory()->getName());
        }
        return $this;
    }
}
