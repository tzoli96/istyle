/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'jquery',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils'
    ],
    function (Component, ko, $, redirectOnSuccessAction, validators, url, quote, totals, priceUtils) {
        'use strict';

        return Component.extend({
            totals: quote.getTotals(),

            defaults: {
                template: "Oander_HelloBankPayment/payment/hellobank-payment-method",
                response: '',
            },

            initialize: function () {
                this._super();
                return this;
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'response'
                    ]);
                return this;
            },

            getCode: function () {
                return 'hellobank';
            },

            getData: function () {
                var data = {
                    'method': this.item.method,
                    'additional_data': {
                        'response': this.response()
                    }
                };
                return data;
            },

            getPaymentLogoSrc: function () {
                return window.checkoutConfig.payment.hellobank.logoSrc;
            },

            validate: function () {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            checkForm: function () {
                if (this.validate() && validators.validate()) {
                    this.placeOrder();
                }
            },

            getResponse: function () {
                return _.map(window.checkoutConfig.payment.hellobank.response, function (value, key) {
                    return {
                        'value': key,
                        'response': value
                    }
                });
            },

            getPrice: function () {
                return totals.getSegment('grand_total').value;
            },

            getSellerId: function () {
                return window.checkoutConfig.payment.hellobank.sellerId;
            },

            getOrderId: function () {
                return window.checkoutConfig.payment.hellobank.orderId;
            },

            getFormattedPrice: function (price) {
                var priceFormat = {
                    decimalSymbol: '.',
                    groupLength: 3,
                    groupSymbol: ",",
                    integerRequired: false,
                    pattern: "$%s",
                    precision: 2,
                    requiredPrecision: 2
                };

                return priceUtils.formatPrice(price, priceFormat);
            },

            getBarems: function () {
                var self = this;
                var barems = window.checkoutConfig.payment.hellobank.barems;
                var filteredBarems = [];

                $(barems).each(function (key, value) {
                    if (parseInt(value.min_price) <= self.getPrice()) {
                        if (parseInt(value.equity) >= 0) {
                            if (value.max_price >= self.getPrice()) {
                                filteredBarems.push(value);
                            }
                        }
                        else {
                            filteredBarems.push(value);
                        }
                    }
                });

                return filteredBarems.sort(function (a, b) {
                    return a.priority - b.priority;
                });
            },

            getInstallmentsByBaremId: function (id) {
                var barems = this.getBarems();
                var installments = [];

                $(barems).each(function (key, value) {
                    if (value.barem_id == id) installments = value.installments.split(',');
                });

                return installments;
            },

            getMinLoan: function (max) {
                return (parseInt(this.getPrice()) - parseInt(max));
            },

            getTrim: function (value) {
                return value.trim();
            },

            parseInt: function (value) {
                return parseInt(value);
            },

            helloPlaceOrder: function () {
                var loanUrl = 'https://www.cetelem.cz/cetelem2_webshop.php/zadost-o-pujcku/on-line-zadost-o-pujcku';
                var calculatedData = window.calculatedData;

                var values = $(calculatedData).find('vysledek');
                var value = {
                    kodProdejce: window.checkoutConfig.payment.hellobank.sellerId,
                    kodBaremu: values.find('kodBaremu'),
                    kodPojisteni: values.find('kodPojisteni'),
                    cenaZbozi: values.find('cenaZbozi'),
                    primaPlatba: values.find('primaPlatba'),
                    vyseUveru: values.find('vyseUveru'),
                    pocetSplatek: values.find('pocetSplatek').text(),
                    odklad: values.find('odklad'),
                    vyseSplatky: values.find('vyseSplatky').text(),
                    cenaUveru: values.find('cenaUveru').text(),
                    RPSN: values.find('RPSN').text(),
                    ursaz: values.find('ursaz').text(),
                    celkovaCastka: values.find('celkovaCastka').text(),
                    recalc: 0,
                    url_back_ok: url.build('?ok'),
                    url_back_ko: url.build('?ko'),
                };

                var form = $('<form>', { action: loanUrl, method: 'post' });
                $.each(value,
                    function (key, value) {
                        $(form).append(
                            $('<input>', { type: 'hidden', name: key, value: value })
                        );
                    });
                $(form).append(
                    $('<input>', { type: 'hidden', name: 'obj', value: this.getOrderId() })
                );
                $(form).appendTo('body').submit();
            },

            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                redirectOnSuccessAction.redirectUrl = url.build('hellobank/payment/redirect/');
                this.redirectAfterPlaceOrder = true;
            },
        });
    }
);
