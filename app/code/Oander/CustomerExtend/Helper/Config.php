<?php
/**
 * Oander_CustomerExtend
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\CustomerExtend\Helper;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Oander\CustomerExtend\Enum\AddressAttributeEnum;

/**
 * Class Config
 *
 * @package Oander\CustomerExtend\Helper
 */
class Config extends AbstractHelper
{
    /**
     * @return array
     */
    public function getAddressAttributePosition($attribute = null)
    {
        $value = (string)$this->scopeConfig->getValue(
            \Oander\CustomerExtend\Enum\Config::PATH_CUSTOMER_ADDRESS_ORDER,
            ScopeInterface::SCOPE_STORE
        );

        if($value != '') {
            try {
                $attributes = unserialize($value);
                $value = [];
                if (is_array($attributes)) {
                    $value = [];
                    foreach ($attributes as $attribute) {
                        $value[$attribute[AddressAttributeEnum::COLUMN_ATTRIBUTE]] = [
                            AddressAttributeEnum::COLUMN_INDIVIDUAL_POSITION => empty($attribute[AddressAttributeEnum::COLUMN_INDIVIDUAL_POSITION])? null: (int)$attribute[AddressAttributeEnum::COLUMN_INDIVIDUAL_POSITION],
                            AddressAttributeEnum::COLUMN_COMPANY_POSITION => empty($attribute[AddressAttributeEnum::COLUMN_COMPANY_POSITION])? null: (int)$attribute[AddressAttributeEnum::COLUMN_COMPANY_POSITION],
                            AddressAttributeEnum::COLUMN_DEFAULT_POSITION => empty($attribute[AddressAttributeEnum::COLUMN_DEFAULT_POSITION])? null: (int)$attribute[AddressAttributeEnum::COLUMN_DEFAULT_POSITION],
                            AddressAttributeEnum::COLUMN_WIDTH => empty($attribute[AddressAttributeEnum::COLUMN_WIDTH])? null: (int)$attribute[AddressAttributeEnum::COLUMN_WIDTH],
                        ];
                    }
                }

            } catch (\Exception $e){
                $value = [];
            }
        }

        return $value;
    }
}
