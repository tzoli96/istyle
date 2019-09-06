<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Plugin\Catalog\Helper;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Oander\IstyleCustomization\Helper\Config;

/**
 * Class Output
 * @package Oander\IstyleCustomization\Plugin\Catalog\Helper
 */
class Output
{
    const ATTRIBUTE_CODE = 'description';

    const DESCRIPTION_TYPE_WIDGET = 'widget';
    const DESCRIPTION_TYPE_RIVER = 'river';
    const DESCRIPTION_TYPE_BASIC = 'basic';

    const DESCRIPTION_WIDGET_ID = '{{widget type="Oander\ProductDescriptionWidget\Block\Widget\ProductDescription"';

    const BASE64_TEMPLATE = 'Oander_IstyleCustomization::product/description/base64_description.phtml';
    const BASE64_VAR_TYPE = 'type';
    const BASE64_VAR_COLOR = 'color';
    const BASE64_VAR_HTML = 'base64-html';

    const OPEN_BUTTON_TEMPLATE = 'Oander_IstyleCustomization::product/description/open_btn.phtml';

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Config
     */
    private $config;

    private $riverSeparateTags = [
        'white' => '<!-- river-end-white -->',
        'black' => '<!-- river-end-black -->'
    ];

    private $riverSeparateColor;

    /**
     * Output constructor.
     * @param LayoutInterface $layout
     * @param Config $config
     */
    public function __construct(
        LayoutInterface $layout,
        Config $config
    )
    {

        $this->layout = $layout;
        $this->config = $config;
    }

    /**
     * @param \Magento\Catalog\Helper\Output $subject
     * @param callable $proceed
     * @param $product
     * @param $attributeHtml
     * @param $attributeName
     * @return mixed
     */
    public function aroundProductAttribute(\Magento\Catalog\Helper\Output $subject, callable $proceed, $product, $attributeHtml, $attributeName)
    {
        $result = $proceed($product, $attributeHtml, $attributeName);

        if ($attributeName === self::ATTRIBUTE_CODE) {

            if (!$this->config->isBasicDescriptionLazyLoadEnabled()
                && !$this->config->isRiverDescriptionLazyLoadEnabled()
                && !$this->config->isWidgetDescriptionLazyLoadEnabled()
            ) {
                return $result;
            }

            $type = $this->getDescriptionType($attributeHtml);

            $shortenedDescription = false;
            if ($type === self::DESCRIPTION_TYPE_BASIC && $this->config->isBasicDescriptionLazyLoadEnabled()) {
                $shortenedDescription = $this->splitDescription($result);
            } elseif ($type === self::DESCRIPTION_TYPE_RIVER && $this->config->isRiverDescriptionLazyLoadEnabled()) {
                $separateTag = $this->findRiverSeparateTag($result);
                if ($separateTag) {
                    $shortenedDescription = substr($result, 0, strpos($result, $separateTag));
                }
            } elseif ($type === self::DESCRIPTION_TYPE_WIDGET && $this->config->isWidgetDescriptionLazyLoadEnabled()) {
                $descriptionWidgets = explode(self::DESCRIPTION_WIDGET_ID, $attributeHtml);
                if (isset($descriptionWidgets[$this->config->getWidgetDescriptionMaxBlocks()])) {
                    $shortenedDescription = '';
                    for ($i = 0; $i <= $this->config->getWidgetDescriptionMaxBlocks(); $i++) {
                        $shortenedDescription .= self::DESCRIPTION_WIDGET_ID . $descriptionWidgets[$i];
                    }
                    $shortenedDescription = $proceed($product, $shortenedDescription, $attributeName);
                }
            }

            if ($shortenedDescription !== false) {
                $result = $this->closeHtmlTags($shortenedDescription) .
                    $this->generateBase64Tag($result, $type);
            }
        }

        return $result;
    }

    /**
     * @param $description
     * @return string
     */
    private function getDescriptionType($description)
    {
        if (strpos($description, self::DESCRIPTION_WIDGET_ID) !== false) {
            return self::DESCRIPTION_TYPE_WIDGET;
        }

        if ($this->findRiverSeparateTag($description) !== false) {
            return self::DESCRIPTION_TYPE_RIVER;
        }

        if ($this->isBasicDescription($description)) {
            return self::DESCRIPTION_TYPE_BASIC;
        }

        return false;
    }

    /**
     * @param $description
     * @return bool|string
     */
    private function splitDescription($description)
    {
        $style = '';

        $descriptionTmp = explode('<style>', $description);
        if (isset($descriptionTmp[1])) {
            $descriptionTmp = explode('</style>', $descriptionTmp[1]);
            if (isset($descriptionTmp[1])) {
                $style = '<style>' . $descriptionTmp[0] . '</style>';
                $description = $descriptionTmp[1];
            }
        }
        if (strlen($description) > $this->config->getBasicDescriptionMaxChars()) {
            $description = substr($description, 0, $this->config->getBasicDescriptionMaxChars() - 1);
            if (substr_count($description, '<') > substr_count($description, '>')) {
                while (substr($description, -1) != '>') {
                    $description = substr($description, 0, -1);
                }
            } else {
                while (substr($description, -1) != ' ') {
                    $description = substr($description, 0, -1);
                }
            }
            return $style . rtrim($description) . $this->config->getBasicDescriptionPostfix();
        }
        return false;
    }

    /**
     * @param $description
     * @return string
     */
    private function isBasicDescription($description)
    {
        $rejectedTags = $this->config->getBasicDescriptionRejectedTags();
        foreach ($rejectedTags as $rejectedTag) {
            if (strpos($description, '<' . $rejectedTag) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $description
     * @return string
     */
    private function findRiverSeparateTag($description)
    {
        foreach ($this->riverSeparateTags as $color => $separateTag) {
            if (strpos($description, $separateTag) !== false) {
                $this->riverSeparateColor = $color;
                return $separateTag;
            } elseif (strpos($description, htmlspecialchars($separateTag)) !== false) {
                $this->riverSeparateColor = $color;
                return htmlspecialchars($separateTag);
            } elseif (strpos($description, trim($separateTag)) !== false) {
                $this->riverSeparateColor = $color;
                return trim($separateTag);
            } elseif (strpos($description, trim(htmlspecialchars($separateTag))) !== false) {
                $this->riverSeparateColor = $color;
                return trim(htmlspecialchars($separateTag));
            }
        }

        return false;
    }

    private function getRiverColor($description)
    {
        if ($this->riverSeparateColor === null) {
            $this->findRiverSeparateTag($description);
        }

        return $this->riverSeparateColor;
    }

    /**
     * @param $description
     * @param $type
     * @return mixed|string
     */
    private function generateBase64Tag($description, $type)
    {
        return $this->layout->createBlock(Template::class)
            ->setData(self::BASE64_VAR_HTML, base64_encode($description))
            ->setData(self::BASE64_VAR_TYPE, $type)
            ->setData(self::BASE64_VAR_COLOR, $this->getRiverColor($description))
            ->setTemplate(self::BASE64_TEMPLATE)
            ->toHtml();
    }

    /**
     * @param $description
     * @return string
     */
    private function closeHtmlTags($description)
    {
        preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $description, $result);
        $openedTags = $result[1];
        preg_match_all('#</([a-z]+)>#iU', $description, $result);
        $closedTags = $result[1];
        $lenOpened = count($openedTags);
        if (count($closedTags) == $lenOpened) {

            return $description;
        }

        $openedTags = array_reverse($openedTags);
        for ($i = 0; $i < $lenOpened; $i++) {
            if (!in_array($openedTags[$i], $closedTags)) {
                $description .= '</' . $openedTags[$i] . '>';
            } else {
                unset($closedTags[array_search($openedTags[$i], $closedTags)]);
            }
        }

        return $description;
    }
}
