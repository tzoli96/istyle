define(
    [
        'underscore',
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'Oney_ThreeByFour/js/model/config'
    ],
    function (_, Component, rendererList, config) {
        "use strict";
        if (config.facilypay_methods && _.isArray(config.facilypay_methods)) {
            config.facilypay_methods.forEach(function (value) {
                rendererList.push(value);
            });
        }
        return Component.extend({});
    }
);
