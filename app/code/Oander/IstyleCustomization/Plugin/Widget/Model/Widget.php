<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Plugin\Widget\Model;

/**
 * Class Widget
 * @package Oander\IstyleCustomization\Plugin\Widget\Model
 */
class Widget
{
    /**
     * @param \Magento\Widget\Model\Widget $subject
     * @param $type
     * @param array $params
     * @param bool $asIs
     */
    public function beforeGetWidgetDeclaration(\Magento\Widget\Model\Widget $subject, $type, $params = [], $asIs = true)
    {
        if (($type === \Magento\CatalogWidget\Block\Product\ProductsList::class
                || $type === \Magento\Catalog\Block\Product\Widget\NewWidget::class
            ) && array_key_exists('link', $params)
        ) {
            $params['link'] = str_replace('"',"'",$params['link']);
        }

        return [
            $type,
            $params,
            $asIs
        ];
    }
}
