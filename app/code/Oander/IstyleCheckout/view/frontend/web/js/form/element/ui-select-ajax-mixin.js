define([
    'jquery',
    'ko',
    "uiRegistry",
    'Magento_Ui/js/form/element/ui-select'
], function ($, ko, registry, uiSelect) {
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
    };

    return function (target) {
        return target.extend(mixin);
    };
});
