define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/select'
], function ($, _, select) {
    'use strict';

    return select.extend({
        defaults: {
            type: '',
            imports: {
                type: '${ $.provider }:data.product.calculator_type',
            },
            endpoints: {
                baseUrl: window.location.protocol + '//' + window.location.hostname, // mage/url is not working with admin url :(
                hellobank: {
                    barems: '/admin/minicart/endpoints/barems',
                }
            }
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
                'type'
            );

            return this;
        },

        /**
         * Init subscribers
         */
        initSubscribers: function () {
            var self = this;

            self.checkVisibilityByValue(self.type());

            self.type.subscribe(
                function (type) {
                    self.setAjaxResponse(type);
                    self.checkVisibilityByValue(type);
                }
            );
        },

        /**
         * Check select visibility by value
         * @param {string || null} value
         */
        checkVisibilityByValue: function (value) {
            return false;
        },

        /**
         * Set ajax response
         * @param {*} type
         */
        setAjaxResponse: function (type) {
            var self = this;
            var data = [];

            if (type == 'hellobank') {
                if (xhr && xhr.readyState != null) xhr.abort();

                var xhr = $.ajax({
                    url: self.endpoints.baseUrl + self.endpoints[type].barems,
                    type: 'GET',
                    data: { type: type},
                    dataType: 'json'
                }).done(function (response) {
                    self.parseBarems(response).forEach(function (item) {
                        data.push({
                            label: item.label,
                            value: item.value
                        });
                    });

                    self.setOptions(data);
                });
            }
            else {
                self.setOptions(data);
            }
        },

        /**
         * Parse barems
         * 
         * @param {*} data 
         * @returns {Array}
         */
        parseBarems: function (data) {
            var barems = [];

            data.forEach(function (value) {
                barems.push({
                    label: value.label,
                    value: value.value
                });
            });

            return barems;
        }
    });
});
