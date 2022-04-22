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
            apiUrl: null,
            value: ''
        },

        /**
         * Initializes UISelect component.
         *
         * @returns {UISelect} Chainable.
         */
        initialize: function () {
            const self = this;
            this.value = '';
            this._super();
            this.initialValue = '';
            this.disabled(true);

            registry.get(self.koSelector, function (element) {
                element.value.subscribe(function (value) {
                    self.ajaxRequestHandler(value)
                }, this)
            });

            return this;
        },

        /**
         * Check selected elements
         *
         * @returns {Boolean}
         */
        hasData: function () {
            if (!this.value()) {
                this.value('');
            }

            return this.value() ? !!this.value().length : false;
        },

        /**
         * Set caption
         */
        setCaption: function () {
            var length;

            if (!_.isArray(this.value()) && this.value()) {
                length = 1;
            } else if (this.value()) {
                length = this.value().length;
            } else {
                this.value('');
                length = 0;
            }

            if (length > 1) {
                this.placeholder(length + ' ' + this.selectedPlaceholders.lotPlaceholders);
            } else if (length && this.getSelected().length) {
                this.placeholder(this.getSelected()[0].label);
            } else {
                this.placeholder(this.selectedPlaceholders.defaultPlaceholder);
            }

            return this.placeholder();
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
                    self.disabled(false);
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
