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

    public function beforePlace_order(
        \StripeIntegration\Payments\Api\Service $subject,
        $result,
        $location
    ) {
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
