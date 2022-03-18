define([
    'jquery',
    'ko',
    "uiRegistry",
    'Magento_Ui/js/form/element/ui-select',
], function ($, ko, registry, uiSelect) {
    'use strict';

    return uiSelect.extend({
        defaults: {
            koSelector: null,
            apiUrl: null
        },

        /**
         * Initializes UISelect component.
         *
         * @returns {UISelect} Chainable.
         */
        initialize: function () {
            const self = this;
            this._super();

            registry.get(self.koSelector, function (element) {
                element.value.subscribe(function (value) {
                    self.ajaxRequestHandler(value)
                }, this)
            });

            return this;
        },

        /**
         * Handling ajax request input parameter
         *
         * @returns {Void}.
         * @param {String}.
         */
        ajaxRequestHandler: function(param) {
            const self = this;
            const response = [];

            $.ajax({
                url: this.apiUrl + param,
                type: 'GET',
                dataType: 'json'
            }).done(function (data) {
                if (data) {
                    $.each(data, function (index, value) {
                        response.push({
                            label: value,
                            level: 0,
                            path: '',
                            value: value
                        });
                    });
                }

                self.reset();
                self.cacheOptions.plain = _.compact(null);
                self.options(response);
            })
            return this;
        }
    })
});
