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

namespace Oander\CustomerAddressValidation\Model\Config\Source;

class ZipcitySyncMode implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => '0', 'label' => __('cron')],['value' => '1', 'label' => __('command')]];
    }

    public function toArray()
    {
        return ['0' => __('cron'),'1' => __('command')];
    }
}
