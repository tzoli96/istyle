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

class Product
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


}