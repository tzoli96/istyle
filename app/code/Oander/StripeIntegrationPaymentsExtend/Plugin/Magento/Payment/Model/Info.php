<?php
/**
 * Bigfishpaymentgateway Extend
 * Copyright (C) 2019 
 * 
 * This file is part of Oander/StripeIntegrationPaymentsExtend.
 * 
 * Oander/StripeIntegrationPaymentsExtend is free software: you can redistribute it and/or modify
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

namespace Oander\StripeIntegrationPaymentsExtend\Plugin\Magento\Payment\Model;

class Info
{

    public function beforeSetAdditionalInformation(
        \Magento\Payment\Model\Info $subject,
        $key,
        $value = null
    ) {
        if($key=="prapi_location" && $value==\Oander\StripeIntegrationPaymentsExtend\Plugin\StripeIntegration\Payments\Api\Service::LOCATION_CHECKOUT)
            return [$key, "checkout"];
        return [$key, $value];
    }
}
