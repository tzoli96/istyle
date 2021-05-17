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

namespace Oander\CustomerAddressValidation\Controller\Adminhtml\Zipcity;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Sync extends \Magento\Backend\App\Action
{
    /**
     * @var \Oander\CustomerAddressValidation\Helper\SyncZipCity
     */
    private $sync;

    /**
     * Generate constructor.
     * @param Action\Context $context
     * @param \Oander\CustomerAddressValidation\Helper\SyncZipCity $sync
     */
    public function __construct(
        Action\Context $context,
        \Oander\CustomerAddressValidation\Helper\SyncZipCity $sync
    ) {
        parent::__construct($context);
        $this->sync = $sync;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = [__('No store was selected')];
        if($this->getRequest()->getParam('store'))
        {
            try
            {
                $result = $this->sync->syncStore($this->getRequest()->getParam('store'));
                if($result)
                    $data = [__('Store zipcity database successfully updated')];
                else
                    $data = [__('Error during store zipcity database updated')];
            }
            catch(\Exception $e)
            {
                $data = array($e->getMessage());
            }
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($data);
        return $resultJson;
    }
}