<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Oander\ImportM2\Enum\Config as ConfigEnum;
use Oander\ImportM2\Helper\Config\General;
use Oander\ImportM2\Model\Resource\Donor\StoreDonor;

/**
 * Class Data
 *
 * @package Oander\ImportM2\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $storePairs = [];
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreDonor
     */
    private $storeDonor;

    /**
     * Data constructor.
     *
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     * @param Config                $config
     * @param StoreDonor            $storeDonor
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Config $config,
        StoreDonor $storeDonor
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->storeDonor = $storeDonor;
    }

    public function getStorePairs()
    {
        if (empty($this->storePairs)) {
            $donorStoreIds =  $this->config->getDonorStoreIds();
            $donorStores = $this->storeDonor->getStores();
            foreach ($donorStores as $donorStore) {
                foreach ($donorStoreIds as $donorStoreId) {
                    if ($donorStore['store_id'] == $donorStoreId) {
                        $currentStore = $this->storeManager->getStore($donorStore['code']);
                        $this->storePairs[$donorStore['code']] = [
                            'donor_id' => $donorStoreId,
                            'current_id' =>$currentStore->getId()
                        ];
                    }
                }
            }

        }

        return $this->storePairs;
    }

    public function getCurrentStoreId($donorStoreId)
    {
        $donorStores = $this->getStorePairs();
        foreach ($donorStores as $donorStore) {
            if ($donorStore['donor_id'] == $donorStoreId) {
                return $donorStore['current_id'];
            }
        }
    }
}
