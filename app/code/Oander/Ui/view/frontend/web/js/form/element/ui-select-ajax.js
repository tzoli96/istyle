define([
    'jquery',
    'Magento_Ui/js/form/element/ui-select',
    'Oander_Ui/js/form/ui-select-state',
    'mage/translate',
    'ko'
], function ($, uiSelect, uiSelectState, $t, ko) {
    'use strict';

    return uiSelect.extend({
        defaults: {
            apiUrl: null
        },

        /**
         * Initializes UISelect component.
         *
         * @returns {UISelect} Chainable.
         */
        initialize: function () {
            this._super();

            uiSelectState.selectedState.subscribe(function (data) {
                // if (!data) {
                //     console.log('no data, make it disable')
                // } else {
                //     console.log('data aviailable make it active')
                // }
                //console.log('state value changed', data);
                this.getRegion(data.value);
            }, this)

            return this;
        },

        getRegion: function(city) {
            const getCountiesAjaxUrl = this.apiUrl + city;
            const response = [];
            const self = this;

            $.ajax({
                url: getCountiesAjaxUrl,
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
                self.options(response);
            })
            return this;
        }
    })
});
