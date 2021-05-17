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

namespace Oander\CustomerAddressValidation\Model;

use Magento\Framework\Exception\AuthorizationException;

class GetCityByZipManagement implements \Oander\CustomerAddressValidation\Api\GetCityByZipManagementInterface
{

    /**
     * @var \Oander\CustomerAddressValidation\Helper\SyncZipCity
     */
    private $sync;
    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $request;
    /**
     * @var \Oander\CustomerAddressValidation\Helper\QueryZipCity
     */
    private $query;
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * GetCityByZipManagement constructor.
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param \Oander\CustomerAddressValidation\Helper\SyncZipCity $sync
     * @param \Oander\CustomerAddressValidation\Helper\QueryZipCity $query
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        \Oander\CustomerAddressValidation\Helper\SyncZipCity $sync,
        \Oander\CustomerAddressValidation\Helper\QueryZipCity $query,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->request = $request;
        $this->sync = $sync;
        $this->query = $query;
        $this->redirect = $redirect;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getCityByZip()
    {
        $redirectUrl = $this->redirect->getRedirectUrl();
        if(strpos($redirectUrl, $this->storeManager->getStore()->getBaseUrl()) !== false) {
            if (strpos($redirectUrl, "checkout") !== false) {
                $countrycode = $this->request->getParam("countrycode");
                $city = $this->request->getParam("zipcode");
                if (!is_string($city) || !is_string($countrycode)) {
                    return;
                }
                return $this->query->getCityByZip($countrycode, $city);
            }
        }
        throw new AuthorizationException(__("Not allowed"));
    }
}
