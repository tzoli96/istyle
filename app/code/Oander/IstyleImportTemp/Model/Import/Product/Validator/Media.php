<?php
/**
 * Oander_IstyleImportTemp
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleImportTemp\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\Validator\Media as MagentoMedia;

/**
 * Class Media
 *
 * @package Oander\IstyleImportTemp\Model\Import\Product\Validator
 */
class Media extends MagentoMedia
{
    /**
     * @param string $string
     * @return bool
     */
    protected function checkPath($string)
    {
        return true; //preg_match(self::PATH_REGEXP, $string);
    }
}
