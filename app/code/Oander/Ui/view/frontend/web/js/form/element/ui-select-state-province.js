define([
    'jquery',
    'Magento_Ui/js/form/element/ui-select',
    'Oander_Ui/js/form/ui-select-state',
], function ($, uiSelect, uiSelectState, $t, ko) {
    'use strict';

    return uiSelect.extend({
        toggleOptionSelected: function (data) {
            this._super();

            uiSelectState.selectedState(data);

            return this;
        },
    })
});
