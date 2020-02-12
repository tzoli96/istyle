<?php
/**
 * BIG FISH Ltd.
 * http://www.bigfish.hu
 *
 * @title      BIG FISH Payment Gateway module for Magento 2
 * @category   BigFish
 * @package    Bigfishpaymentgateway_Pmgw
 * @author     BIG FISH Ltd., paymentgateway [at] bigfish [dot] hu
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2017, BIG FISH Ltd.
 */
namespace Oander\IstyleCustomization\Bloc;

use Magento\Sales\Model\Order;

/**
 * Class Success
 * @package Oander\IstyleCustomization\Bloc
 */
class Success extends \Bigfishpaymentgateway\Pmgw\Block\Success
{
    /**
     * @param Order $order
     * @return bool
     */
    private function canViewOrder(Order $order)
    {
        return false;
    }

}
