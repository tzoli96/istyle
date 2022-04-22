define([
    'jquery',
    'ko',
    "uiRegistry",
    'Magento_Ui/js/form/element/ui-select',
    'Oander_IstyleCheckout/js/model/store',
    'Oander_IstyleCheckout/js/view/billing-address/validate',
], function ($, ko, registry, uiSelect, store, validate) {
    'use strict';

    var mixin = {
        initialize: function () {
            this._super();
            const self = this;
            let initValue = null;

            const optionsValueSubscriber = this.options.subscribe(function () {
                if (initValue) {
                    self.value(initValue);
                }
                optionsValueSubscriber.dispose();
            }.bind(this));

            const initValueSubscriber = this.value.subscribe(function (value) {
                if (!self.options().length) {
                    initValue = value;
                }
                initValueSubscriber.dispose();
            }.bind(this));
        },

        validateHandler: function () {
            var key = 'billingAddressshared.city';
            var fieldElement = $('[name="' + key + '"] .form-control');

            validate.requiredHandler(fieldElement, key);
        },

        reset: function () {
            this._super();
            var self = this;
            var currentLS = store.getLocalStorage();

            store.steps.active.subscribe(function (value) {
                if (value === 'billingAddress') self.validateHandler();
            }, this);

            if (currentLS.steps) {
                if (currentLS.steps.active === 'billingAddress') self.validateHandler();
            }
        },

        toggleOptionSelected: function () {
            this._super();
            var self = this;
            var currentLS = store.getLocalStorage();
            var shippingElem = registry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.city');
            var billingElem = registry.get('checkout.steps.billing-step.payment.afterMethods.billing-address-form.form-fields.city');

            store.steps.active.subscribe(function (value) {
                if (value === 'billingAddress') self.validateHandler();
            }, this);

            if (currentLS.steps) {
                if (currentLS.steps.active === 'billingAddress') self.validateHandler();
            }

            if (shippingElem) shippingElem.filterInputValue('');
            if (billingElem) billingElem.filterInputValue('');

            return this;
        },

        filterOptionsList: function () {
            var value = this.filterInputValue().trim().toLowerCase(),
                array = [];

            if (value && value.length < 2) {
                return false;
            }

            this.cleanHoveredElement();

            if (!value) {
                this._setItemsQuantity(false);
            }

            this.showPath ? this.renderPath = true : false;

            if (this.filterInputValue()) {
                array = this.selectType === 'optgroup' ?
                    this._getFilteredArray(this.cacheOptions.lastOptions, value) :
                    this._getFilteredArray(this.cacheOptions.plain, value);

                if (!value.length) {
                    this.options(this.cacheOptions.plain);
                    this._setItemsQuantity(this.cacheOptions.plain.length);
                } else {
                    this.options(array);
                    this._setItemsQuantity(array.length);
                }

                return false;
            }

            this.options(this.cacheOptions.plain);
        },

        /**
         * Toggle list visibility
         *
         * @returns {Object} Chainable
         */
         toggleListVisible: function (data, event) {
            var currentLS = store.getLocalStorage();

            this.listVisible(!this.listVisible());

            store.steps.active.subscribe(function (value) {
                if (value === 'shippingAddress') {
                    $(this.cacheUiSelect).find('.oander-ui-control-text').focus();
                }

                if (value === 'billingAddress') {
                    $('[name="billingAddressshared.city"]').find('.oander-ui-control-text').focus();
                }
            }, this);

            if (currentLS.steps) {
                if (currentLS.steps.active === 'shippingAddress') {
                    $(this.cacheUiSelect).find('.oander-ui-control-text').focus();
                }

                if (currentLS.steps.active === 'billingAddress') {
                    $('[name="billingAddressshared.city"]').find('.oander-ui-control-text').focus();
                }
            }

            return this;
        },
    };

    return function (target) {
        return target.extend(mixin);
    };
});
