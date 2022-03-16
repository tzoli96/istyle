define([
    'jquery',
    'Magento_Ui/js/form/element/ui-select',
    'mage/translate',
    'ko'
], function ($, uiSelect, $t, ko) {
    'use strict';

    return uiSelect.extend({
        defaults: {
            koSelector: null,
            apiUrl: null,
            options: ko.observableArray(["valami", "kutya"])
        },

        /**
         * Initializes UISelect component.
         *
         * @returns {UISelect} Chainable.
         */
        initialize: function () {
            this._super();

            $.async(
                this.rootListSelector,
                this,
                this.onRootListRender.bind(this),
            );

            this.getRegion();

            return this;
        },

        getRegion: function() {
            const getCountiesAjaxUrl = this.apiUrl;

            $.ajax({
                url: getCountiesAjaxUrl + 'Alba',
                type: 'GET',
                dataType: 'json'
            }).done(function (data) {
                    let response = data;
            })
        },
        /**
         * Parses options and merges the result with instance
         * Set defaults according to mode and levels configuration
         *
         * @param  {Object} config
         * @returns {Object} Chainable.
         */
        initConfig: function (config) {
            var result = this.options;

            this._super();
            return this;
        },
    })
});
