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
 * @author  Róbert Betlen  <robert.betlen@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */
namespace Oander\IstyleCustomization\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

/**
 * Class ReadOnlyAttributes
 * @package Oander\IstyleCustomization\Ui\DataProvider\Product\Form\Modifier
 */
class ReadOnlyAttributes extends AbstractModifier
{

    /**
     * Entity
     *
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * ReadOnlyAttributes constructor.
     * @param ArrayManager $arrayManager
     * @param LocatorInterface $locator
     */
    public function __construct(
        ArrayManager $arrayManager,
        LocatorInterface $locator
    ){
        $this->arrayManager = $arrayManager;
        $this->locator = $locator;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {

        $sku = $this->locator->getProduct()->getSku();

        if($sku != null) {

            $attribute = 'sku';

            $path = $this->arrayManager->findPath($attribute, $meta, null, 'children');
            $meta = $this->arrayManager->set(
                "{$path}/arguments/data/config/disabled",
                $meta,
                true
            );

        }

        return $meta;

    }

}