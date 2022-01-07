<?php
/**
 *   /$$$$$$   /$$$$$$  /$$   /$$ /$$$$$$$  /$$$$$$$$ /$$$$$$$
 *  /$$__  $$ /$$__  $$| $$$ | $$| $$__  $$| $$_____/| $$__  $$
 * | $$  \ $$| $$  \ $$| $$$$| $$| $$  \ $$| $$      | $$  \ $$
 * | $$  | $$| $$$$$$$$| $$ $$ $$| $$  | $$| $$$$$   | $$$$$$$/
 * | $$  | $$| $$__  $$| $$  $$$$| $$  | $$| $$__/   | $$__  $$
 * | $$  | $$| $$  | $$| $$\  $$$| $$  | $$| $$      | $$  \ $$
 * |  $$$$$$/| $$  | $$| $$ \  $$| $$$$$$$/| $$$$$$$$| $$  | $$
 *  \______/ |__/  |__/|__/  \__/|_______/ |________/|__/  |__/
 *
 * StripeIntegration Payments Extend module
 *
 * @author  János Pinczés <janos.pinczes@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\StripeIntegrationPaymentsExtend\Plugin\StripeIntegration\Payments\Api;

class Service
{

    CONST LOCATION_CHECKOUT = "checkout_ext";
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * Service constructor.
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Magento\Checkout\Model\Cart $cart
    )
    {
        $this->cart = $cart;
    }

    public function beforePlace_order(
        \StripeIntegration\Payments\Api\Service $subject,
        $result,
        $location
    ) {
        //Add billing name from cardholder (by default no billing name prodived with googlePay)
        if($result["walletName"] == "googlePay")
        {
            if(empty($result["paymentMethod"]["billing_details"]["name"]))
                $result["paymentMethod"]["billing_details"]["name"] = $result["payerName"];
        }
        if($location=="checkout" && !empty($result["shippingOption"]))
            return [$result, self::LOCATION_CHECKOUT];
        return [$result, $location];
    }

    public function afterGetApplePayParams(
        \StripeIntegration\Payments\Api\Service $subject,
        $result
    ) {
        $result["requestShipping"] = true;
        return $result;
    }

    public function aroundEstimate_cart(
        \StripeIntegration\Payments\Api\Service $subject,
        \Closure $proceed,
        $address
    ) {
        //Add for Shipping restriction payment_method filter
        $this->cart->getQuote()->getShippingAddress()->setPaymentMethod("stripe_payments_express");


        $result = $proceed($address);
        if(isset($address["shippingMethod"])) {
            if (isset($address["shippingMethod"]["carrier_code"]) && isset($address["shippingMethod"]["method_code"])) {

                $result = \Zend_Json::decode($result);

                if (isset($result["results"])) {
                    if (is_array($result["results"])) {
                        $firstMethodId = null;
                        foreach ($result["results"] as $id => $method) {
                            if ($method["id"] == ($address["shippingMethod"]["carrier_code"] . "_" . $address["shippingMethod"]["method_code"])) {
                                $firstMethodId = $id;
                                break;
                            }
                        }
                        if ($firstMethodId) {
                            $firstMethod = $result["results"][$firstMethodId];
                            array_splice($result["results"], $firstMethodId, 1);
                            array_unshift($result["results"], $firstMethod);
                        }
                        $result = \Zend_Json::encode($result);
                    }
                }
            }
        }
        return $result;
    }
}
