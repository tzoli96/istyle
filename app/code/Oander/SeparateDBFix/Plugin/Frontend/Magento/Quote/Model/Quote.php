<?php
/**
 * All rewrites connected to separate DB
 * Copyright (C) 2019
 *
 * This file is part of Oander/SeparateDBFix.
 *
 * Oander/SeparateDBFix is free software: you can redistribute it and/or modify
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

namespace Oander\SeparateDBFix\Plugin\Frontend\Magento\Quote\Model;

class Quote
{
    CONST PREFIX = "oander_qa_";
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Quote constructor.
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    )
    {
        $this->registry = $registry;
    }

    public function aroundGetAddressesCollection(
        \Magento\Quote\Model\Quote $subject,
        \Closure $proceed
    ) {
        if($subject->getEntityId()) {
            $addresses = $this->registry->registry(self::PREFIX . $subject->getEntityId());
            if($addresses)
                $result = $addresses;
            else {
                $result = $proceed();
                $this->registry->register(self::PREFIX . $subject->getEntityId(), $result);
            }
        }
        else
        {
            $result = $proceed();
        }
        return $result;
    }
}