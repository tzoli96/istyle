define([
    'jquery',
    'select2',
    'mage/translate'
], function ($, select2, $t) {
    'use strict';

    const region = $('.ro-region-select');
    const city = $('.ro-city-select');
    const cityAlreadyHasValue = city.attr('data-value');

    if (cityAlreadyHasValue) {
        getCities('onInit');
    } else {
        city.select2({}).prop('disabled', true);
    }

    region.on('change', function () {
        city.trigger('regionChanged');
    });

    city.on('regionChanged', function () {
        getCities();
    });

    function i18n() {
        region.select2({
            language: 'ro'
        });

        $.fn.select2.defaults.set('language', {
            errorLoading: function () {
                return $t('Error loading results');
            },
            inputTooLong: function (args) {
                return $t('Input too long');
            },
            inputTooShort: function (args) {
                return $t('Input too short');
            },
            loadingMore: function () {
                return $t('Loading...');
            },
            maximumSelected: function (args) {
                return $t('Maximum selected');
            },
            noResults: function () {
                return $t('No results');
            },
            searching: function () {
                return $t('Searching');
            }
        });

        // forcing to override i18n strings
        $('select:not([ajax-url])').select2({});
    }
    i18n();

    function getCities(onInit) {
        const getRegionValue = region.val();
        const getCountiesAjaxUrl = '/rest/V1/oander/addresslist/getCityByRegion/';

        $.ajax({
            url: getCountiesAjaxUrl + getRegionValue,
            type: 'GET',
            dataType: 'json'
        }).done(function (data) {
            const response = data;
            const formatResponse = [
                {
                    "id": 0,
                    "text": $t('Please select city'),
                    "disabled": true,
                    "selected": true
                }
            ];

            if (response) {
                $.each(response, function (index, value) {
                    formatResponse.push({
                        id: value,
                        text: value
                    });
                });

                city.empty().select2({
                    data: formatResponse,
                }).prop('disabled', false);

                if (cityAlreadyHasValue && onInit) {
                    city.val(cityAlreadyHasValue).trigger('change');
                }
            }
        });
    }
});
