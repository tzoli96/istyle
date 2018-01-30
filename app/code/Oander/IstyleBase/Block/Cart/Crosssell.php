<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oander\IstyleBase\Block\Cart;

/**
 * Cart crosssell list
 */
class Crosssell extends \Magento\Checkout\Block\Cart\Crosssell
{
    /**
     * Items quantity will be capped to this value
     *
     * @var int
     */
    protected $_maxItemCount = 12;
}
