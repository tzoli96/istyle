define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'raiffeisen',
                component: 'Oander_RaiffeisenPayment/js/view/payment/method-renderer/raiffeisen-method'
            }
        );
        return Component.extend({});
    }
);
