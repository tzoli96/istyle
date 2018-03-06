<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Helper\Config;

use Oander\ImportM2\Enum\Config as ConfigEnum;

/**
 * Trait General
 *
 * @package Oander\ImportM2\Helper\Config
 */
trait General
{
    /**
     * @return array
     */
    public function getDonorStoreIds(): array
    {
        $storeIds = $this->general[ConfigEnum::GENERAL_DONOR_STORE_VIEW] ?? '';
        if (strpos($storeIds, ',') !== false) {
            $storeIds = explode(',',$storeIds);
        }

        return (array)$storeIds;
    }
}
