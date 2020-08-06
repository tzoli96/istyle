<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Oander\FanCourierValidator\Helper\Data;
use Oander\WarehouseManager\Helper\Api;
use Oander\Checkout\Error\VisibleProblemError;
use Magento\Catalog\Model\Product\Type;

/**
 * Class CheckoutSubmitBefore
 * @package Oander\FanCourierValidator\Observer
 */
class CheckoutSubmitBefore implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * @param Data $data
     */
    public function __construct(
        Data $data
    ) {
        $this->data = $data;
    }

    /**
     * @param Observer $observer
     *
     * @throws VisibleProblemError
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        if ($this->data->getValidationLevel() !=''
            && (empty($shippingAddress->getRegion()) || empty($billingAddress->getRegion()))
        ) {
            throw new VisibleProblemError(
                __('%fieldName is a required field.', ['fieldName' => 'region'])
            );
        }
    }
}
