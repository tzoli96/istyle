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
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/action/place-order',
    ],
    function (Component, ko, $, redirectOnSuccessAction, validators, url, quote, totals, priceUtils,placeOrderAction,) {
        'use strict';

        return Component.extend({
            totals: quote.getTotals(),

            defaults: {
                template: "Oander_HelloBankPayment/payment/hellobank-payment-method",
                kodBaremu: '',
                kodPojisteni: '',
                cenaZbozi: '',
                primaPlatba: '',
                vyseUveru: '',
                pocetSplatek: '',
                odklad: '',
                vyseSplatky: '',
                cenaUveru: '',
                RPSN: '',
                ursaz: '',
                celkovaCastka: '',
            },

            initialize: function () {
                this._super();
                return this;
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'kodBaremu',
                        'kodPojisteni',
                        'cenaZbozi',
                        'primaPlatba',
                        'vyseUveru',
                        'pocetSplatek',
                        'odklad',
                        'vyseSplatky',
                        'cenaUveru',
                        'RPSN',
                        'ursaz',
                        'celkovaCastka',
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
                        'kodBaremu': this.getValue('kodBaremu'),
                        'kodPojisteni': this.getValue('kodPojisteni'),
                        'cenaZbozi': this.getValue('cenaZbozi'),
                        'primaPlatba': this.getValue('primaPlatba'),
                        'vyseUveru': this.getValue('vyseUveru'),
                        'pocetSplatek': this.getValue('pocetSplatek'),
                        'odklad': this.getValue('odklad'),
                        'vyseSplatky': this.getValue('vyseSplatky'),
                        'cenaUveru': this.getValue('cenaUveru'),
                        'RPSN': this.getValue('RPSN'),
                        'ursaz': this.getValue('ursaz'),
                        'celkovaCastka': this.getValue('celkovaCastka'),
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
                var self = this;
                return _.map(window.checkoutConfig.payment.hellobank.response, function (value, key) {
                    return {
                        'value': key,
                        'kodBaremu': self.getValue('kodBaremu'),
                        'kodPojisteni': self.getValue('kodPojisteni'),
                        'cenaZbozi': self.getValue('cenaZbozi'),
                        'primaPlatba': self.getValue('primaPlatba'),
                        'vyseUveru': self.getValue('vyseUveru'),
                        'pocetSplatek': self.getValue('pocetSplatek'),
                        'odklad': self.getValue('odklad'),
                        'vyseSplatky': self.getValue('vyseSplatky'),
                        'cenaUveru': self.getValue('cenaUveru'),
                        'RPSN': self.getValue('RPSN'),
                        'ursaz': self.getValue('ursaz'),
                        'celkovaCastka': self.getValue('celkovaCastka'),
                        'recalc': 0,
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

            getValue: function ( key) {
                var calculatedData = $(window.calculatedData).find('vysledek');
                return calculatedData.find(key).text();
            },

            helloPlaceOrder: function (orderId) {
                var loanUrl = 'https://www.cetelem.cz/cetelem2_webshop.php/zadost-o-pujcku/on-line-zadost-o-pujcku';
                var calculatedData = window.calculatedData;

                var values = $(calculatedData).find('vysledek');
                var value = {
                    kodProdejce: window.checkoutConfig.payment.hellobank.sellerId,
                    kodBaremu: values.find('kodBaremu').text(),
                    kodPojisteni: values.find('kodPojisteni').text(),
                    cenaZbozi: values.find('cenaZbozi').text(),
                    primaPlatba: values.find('primaPlatba').text(),
                    vyseUveru: values.find('vyseUveru').text(),
                    pocetSplatek: values.find('pocetSplatek').text(),
                    odklad: values.find('odklad').text(),
                    vyseSplatky: values.find('vyseSplatky').text(),
                    cenaUveru: values.find('cenaUveru').text(),
                    RPSN: values.find('RPSN').text(),
                    ursaz: values.find('ursaz').text(),
                    celkovaCastka: values.find('celkovaCastka').text(),
                    recalc: 0,
                    url_back_ok: url.build('hellobank/payment/kostate'),
                    url_back_ko: url.build('hellobank/payment/okstate'),
                };

                var form = $('<form>', { action: loanUrl, method: 'post' });
                $.each(value,
                    function (key, value) {
                        $(form).append(
                            $('<input>', { type: 'hidden', name: key, value: value })
                        );
                    });
                $(form).append(
                    $('<input>', { type: 'hidden', name: 'obj', value: orderId })
                );
                $(form).appendTo('body').submit();
            },
            /**
             * @override
             */
            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate() && validators.validate()) {
                    this.isPlaceOrderActionAllowed(false);

                    var placeOrder;
                    if (this.getPlaceOrderDeferredObject) {
                        placeOrder = this.getPlaceOrderDeferredObject();
                    } else {
                        placeOrder = $.when(placeOrderAction(this.getData(), this.redirectAfterPlaceOrder, this.messageContainer));
                    }
                    placeOrder
                        .fail(
                            function () {
                                self.preventFormReload = false;
                                self.isPlaceOrderActionAllowed(true);
                            }
                        ).done(
                        function (orderId) {
                            self.helloPlaceOrder(orderId);
                        }
                    );

                    return true;
                }

                return false;
            }
        });
    }
);
