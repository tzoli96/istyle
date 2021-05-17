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

namespace Oander\CustomerAddressValidation\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Oander\CustomerAddressValidation\Enum\Config;

class RefreshZIPCity extends Command
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    private $storeRepository;
    /**
     * @var \Oander\CustomerAddressValidation\Helper\SyncZipCity
     */
    private $sync;

    /**
     * RefreshZIPCity constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Oander\CustomerAddressValidation\Helper\SyncZipCity $sync
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Oander\CustomerAddressValidation\Helper\SyncZipCity $sync,
        $name = null
    )
    {
        parent::__construct($name);
        $this->scopeConfig = $scopeConfig;
        $this->storeRepository = $storeRepository;
        $this->sync = $sync;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $syncMode = $this->scopeConfig->getValue(
            Config::SYNC_MODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if($syncMode) {
            foreach ($this->storeRepository->getList() as $store)
            {
                if($store["store_id"])
                {
                    if(!$this->sync->syncStore($store["store_id"]))
                    {
                        $output->writeln(sprintf("Error during sync storeId %s, please refer to log for more details", $store["store_id"]));
                    }
                }
            }
            $output->writeln("Executed");
        }
        else
        {
            $output->writeln("Command line execution disabled");
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("oander_customeraddressvalidation:refreshzipcity");
        $this->setDescription("Refresh ZIP City database");
        parent::configure();
    }
}
