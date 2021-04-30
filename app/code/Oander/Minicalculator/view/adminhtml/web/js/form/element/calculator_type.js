define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';

    return select.extend({

        setOptions: function (data) {
            //console.log(data);
            return this._super();
        },
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            //console.log('Selected Value: ' + value);
            return this._super();
        },
    });
});
