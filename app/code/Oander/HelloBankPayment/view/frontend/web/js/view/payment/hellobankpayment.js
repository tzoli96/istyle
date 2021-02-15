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
                type: 'hellobank',
                component: 'Oander_HelloBankPayment/js/view/payment/method-renderer/hellobank-method'
            }
        );
        return Component.extend({});
    }
);
