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
            koSelector: null,
            apiUrl: null,
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

            uiSelectState.selectedState.subscribe(function (value) {
                console.log('state value changed', value);
            });

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
            config.options = [
                {
                    label: 'A',
                    level: 0,
                    path: '',
                    value: 'A',
                },
                {
                    label: 'B',
                    level: 0,
                    path: '',
                    value: 'B',
                },
            ];
            var result = config.options,
                defaults = this.constructor.defaults,
                multiple = _.isBoolean(config.multiple) ? config.multiple : defaults.multiple,
                type = config.selectType || defaults.selectType,
                showOpenLevelsActionIcon = _.isBoolean(config.showOpenLevelsActionIcon) ?
                    config.showOpenLevelsActionIcon :
                    defaults.showOpenLevelsActionIcon,
                openLevelsAction = _.isBoolean(config.openLevelsAction) ?
                    config.openLevelsAction :
                    defaults.openLevelsAction;

            multiple = !multiple ? 'single' : false;
            config.showOpenLevelsActionIcon = showOpenLevelsActionIcon && openLevelsAction;
            _.extend(config, result, defaults.presets[multiple], defaults.presets[type]);
            this._super();

            return this;
        },
    })
});
