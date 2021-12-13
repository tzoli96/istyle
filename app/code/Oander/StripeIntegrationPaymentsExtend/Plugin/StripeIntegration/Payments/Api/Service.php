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
        $result = $proceed();
        if(isset($address["shippingMethod"]))
        {
            if(isset($address["shippingMethod"]["carrier_code"]) && isset($address["shippingMethod"]["method_code"]))
            {
                $firstMethodId = null;
                $result = \Zend_Json::decode($result);
                foreach ($result["results"] as $id => $method)
                {
                    if($method["id"] == $address["shippingMethod"]["carrier_code"] . "_" . isset($address["shippingMethod"]["method_code"]))
                    {
                        $firstMethodId = $id;
                    }
                }
                if($firstMethodId)
                {
                    $firstMethod = $result[$firstMethodId];
                    array_splice($result,$firstMethod,1);
                    array_unshift($result, $firstMethod);
                }
                $result = \Zend_Json::encode($result);
            }
        }
        return $result;
    }
}
