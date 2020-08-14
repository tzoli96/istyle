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
                type: 'cofidis',
                component: 'Oander_CofidisPayment/js/view/payment/method-renderer/cofidis-method'
            }
        );
        return Component.extend({});
    }
);