<?php
namespace Aheadworks\Popup\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PageType
 * @package Aheadworks\Popup\Model\Source
 */
class PageType implements OptionSourceInterface
{
    const HOME_PAGE = 1;
    const PRODUCT_PAGE = 2;
    const CATEGORY_PAGE = 3;
    const SHOPPINGCART_PAGE = 4;
    const CHECKOUT_PAGE = 5;

    const DEFAULT_VALUE = 2;

    /**
     * Get option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $newArray = [];
        $positions = $this->toOptionArray();

        foreach ($positions as $position) {
            $newArray[$position['value']] = $position['label'];
        }
        return $newArray;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::HOME_PAGE,  'label' => __('Home Page')],
            ['value' => self::PRODUCT_PAGE,  'label' => __('Product Pages')],
            ['value' => self::CATEGORY_PAGE,  'label' => __('Catalog Pages')],
            ['value' => self::SHOPPINGCART_PAGE,  'label' => __('Shopping Cart')],
            ['value' => self::CHECKOUT_PAGE,  'label' => __('Checkout')],
        ];
    }

    /**
     * Get option label by type
     *
     * @param mixed $pageType
     * @return null
     */
    public function getLabelByValue($pageType)
    {
        $types = $this->getOptionArray();
        $result = null;
        if (array_key_exists($pageType, $types)) {
            $result = $types[$pageType];
        }
        return $result;
    }
}
