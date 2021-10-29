define(
    [
        'ko',
        'Magento_Checkout/js/checkout-data',
        'Magento_Catalog/js/price-utils'
    ],
    function (ko, checkoutData, priceUtils) {
        "use_strict";
        return {
            facilypay_methods: window.checkoutConfig.payment.oney_facilypay.facilypay_methods,
            getNumber: function (code) {
                if (window.checkoutConfig.payment.oney_facilypay[code]) {
                    return window.checkoutConfig.payment.oney_facilypay[code].number;
                }
                return 'Oney Payments';
            },
            getTitle: function (code) {
                if (window.checkoutConfig.payment.oney_facilypay[code]) {
                    return window.checkoutConfig.payment.oney_facilypay[code].title;
                }
                return 'Oney Payment';
            },
            getPostalRegex: function () {
                return RegExp(window.checkoutConfig.payment.oney_facilypay.postal);
            },
            getPhoneRegex: function () {
                return RegExp(window.checkoutConfig.payment.oney_facilypay.phone);
            },
            getCountry: function () {
                return RegExp(window.checkoutConfig.payment.oney_facilypay.country);
            },
            getError: function (code) {
                return window.checkoutConfig.payment.oney_facilypay.error[code];
            },
            formatPriceForQuote: function (price, quote) {
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },
            useTin: function () {
                return window.checkoutConfig.payment.oney_facilypay.use_tin
            },
            translate: function (text) {
                const translate = window.checkoutConfig.payment.oney_facilypay.translate;
                if(typeof translate[text] !== "undefined"){
                    return translate[text];
                }
                return text;
            }
        }
    }
);
