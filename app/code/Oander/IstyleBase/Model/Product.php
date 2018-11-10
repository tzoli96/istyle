<?php
/**
 * Oander_IstyleBase
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */
declare(strict_types = 1);

namespace Oander\IstyleBase\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Product
 *
 * @package Oander\IstyleBase\Plugin\Catalog\Model
 */
class Product extends \Magento\Catalog\Model\Product
{
    /**
     * Get product status
     *
     * @return int
     */
    public function getStatus()
    {
        $status = $this->_getData(self::STATUS);

        if ($status === null) {
            try {
                $defaultStatus = $this->metadataService->get(\Magento\Catalog\Model\Product::STATUS);
                $status = $defaultStatus->getDefaultValue();
            } catch (NoSuchEntityException $e) {
                $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
            }
        }

        return $status;
    }
}