<?php
/**
 * Customer Address Validate
 * Copyright (C) 2019 
 * 
 * This file is part of Oander/CustomerAddressValidation.
 * 
 * Oander/CustomerAddressValidation is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Oander\CustomerAddressValidation\Cron;

use Oander\CustomerAddressValidation\Enum\Config;

class RefreshZIPCity
{
    protected $logger;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Oander\CustomerAddressValidation\Helper\SyncZipCity
     */
    private $sync;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Oander\CustomerAddressValidation\Helper\SyncZipCity $sync
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Oander\CustomerAddressValidation\Helper\SyncZipCity $sync
    )
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->sync = $sync;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $syncMode = $this->scopeConfig->getValue(
            Config::SYNC_MODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if(!$syncMode) {
            $this->sync->syncAll();
            $this->logger->addInfo("Cronjob refreshZIPCity is executed.");
        }
    }
}
