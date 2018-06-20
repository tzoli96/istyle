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
 * Oander_IstyleBase
 *
 * @author  Nikolett Molnar <nikolett.molnar@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */
declare(strict_types = 1);

namespace Oander\IstyleBase\Plugin\Catalog\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Product
 *
 * @package Oander\IstyleBase\Plugin\Catalog\Model
 */
class Product extends \Magento\Catalog\Model\Product
{
    /**
     * @param             $subject
     * @param \Closure    $method
     *
     * @return bool
     */
    public function aroundIsSalable($subject, \Closure $method): bool
    {
        if($subject->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED){
            return true;
        }else{
            return false;
        }
    }
    /**
     * @param          $subject
     * @param \Closure $method
     *
     * @return bool
     */
    public function aroundIsSaleable($subject, \Closure $method): bool
    {
        if($subject->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get product status
     *
     * @return int
     */
    public function aroundGetStatus($subject, \Closure $method)
    {
        $status = $subject->_getData(\Magento\Catalog\Model\Product::STATUS);

        if ($status === null) {
            try {
                $defaultStatus = $subject->metadataService->get(\Magento\Catalog\Model\Product::STATUS);
                $status = $defaultStatus->getDefaultValue();
            } catch (NoSuchEntityException $e) {
                $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
            }
        }

        return $status;
    }
}