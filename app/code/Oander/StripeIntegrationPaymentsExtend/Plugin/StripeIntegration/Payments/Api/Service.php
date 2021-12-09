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
        if($location=="checkout")
            return [$result, self::LOCATION_CHECKOUT];
        return [$result, $location];
    }
}
