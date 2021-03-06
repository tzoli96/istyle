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

        if ($this->data->getValidationLevel() == 'valid') {
            if (!$this->data->isStateCityValid((string)$shippingAddress->getRegion(),(string)$shippingAddress->getCity())) {
                throw new VisibleProblemError(
                    __('Shipping address city(%city), state(%state) binding is not valid', ['city' => (string)$shippingAddress->getCity(), 'state' => (string)$shippingAddress->getRegion()])
                );
            }

            if (!$this->data->isStateCityValid((string)$billingAddress->getRegion(),(string)$billingAddress->getCity())) {
                throw new VisibleProblemError(
                    __('Billing address city(%city), state(%state) binding is not valid', ['city' => (string)$billingAddress->getCity(), 'state' => (string)$billingAddress->getRegion()])
                );
            }
        }
    }
}
