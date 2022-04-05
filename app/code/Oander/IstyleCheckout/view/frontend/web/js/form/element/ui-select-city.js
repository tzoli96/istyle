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

            store.steps.active.subscribe(function (value) {
                if (value === 'billingAddress') self.validateHandler();
            }, this);

            if (currentLS.steps) {
                if (currentLS.steps.active === 'billingAddress') self.validateHandler();
            }

            return this;
        },
    };

    return function (target) {
        return target.extend(mixin);
    };
});
