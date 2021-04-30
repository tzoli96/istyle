define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/select'
], function ($, _, select) {
    'use strict';

    return select.extend({
        defaults: {
            type: '',
            barem: '',
            imports: {
                type: '${ $.provider }:data.product.calculator_type',
                barem: '${ $.provider }:data.product.calculator_barem',
            },
            endpoints: {
                baseUrl: window.location.protocol + '//' + window.location.hostname, // mage/url is not working with admin url :(
                hellobank: {
                    installments: '/admin/minicart/endpoints/installments',
                }
            },
        },

        /**
         * Initialize
         */
        initialize: function () {
            this._super();
            this.initSubscribers();
        },

        /**
         * Init observable
         *
         * @returns {Object}
         */
        initObservable: function () {
            this._super().observe(
                'type barem'
            );

            return this;
        },

        /**
         * Init subscribers
         */
        initSubscribers: function () {
            var self = this;

            self.checkVisibilityByValue(self.barem());

            self.barem.subscribe(
                function (barem) {
                    self.setAjaxResponse(self.type(), barem);
                    self.checkVisibilityByValue(barem);
                }
            );
        },

        checkVisibilityByValue: function (value) {
            return false;
        },

        setAjaxResponse: function (type, barem) {
            var self = this;
            var data = [];

            if (type && barem) {
                if (type == 'hellobank') {
                    if (xhr && xhr.readyState != null) xhr.abort();

                    var xhr = $.ajax({
                        url: self.endpoints.baseUrl + self.endpoints.hellobank.installments,
                        type: 'GET',
                        data: { type: type, barem: barem },
                        dataType: 'json'
                    }).done(function (response) {
                        if (response.length > 1) {
                            self.parseInstallments(response).forEach(function (item) {
                                data.push({
                                    label: item.label,
                                    value: item.value
                                });
                            });
                        }
                        else {
                            data.push(response);
                        }

                        self.setOptions(data);
                    });
                }
                else {
                    self.setOptions(data);
                }
            }
            else {
                self.setOptions(data);
            }
        },

        /**
         * Parse installments
         * 
         * @param {*} data 
         * @returns {Array}
         */
        parseInstallments: function (data) {
            var installments = [];

            if (Array.isArray(data)) {
                data.forEach(function (value) {
                    installments.push({
                        label: value.label,
                        value: value.value
                    });
                });
            }

            return installments;
        },
    });
});
