<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Oander\ImportM2\Model\Resource\Donor\StoreDonor;

/**
 * Class DonorStoreView
 *
 * @package Oander\ImportM2\Model\Config\Source
 */
class DonorStoreView implements ArrayInterface
{
    /**
     * @var StoreDonor
     */
    private $donor;

    /**
     * DonorStoreView constructor.
     *
     * @param StoreDonor $donor
     */
    public function __construct(StoreDonor $donor) {
        $this->donor = $donor;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = $this->toArray();
        $options = [];

        foreach ($data as $key => $value) {
            $options[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     * @throws \RuntimeException
     */
    public function toArray()
    {
        $stores = $this->donor->getStores();
        $storeCodes = [];
        foreach ($stores as $store) {
            $storeCodes[$store['store_id']] = $store['code'];
        }

        return $storeCodes;
    }
}
