define([
    'jquery',
    'select2',
    'mage/translate'
], function ($, select2, $t) {
    'use strict';

    // init selects
    $('.ro-region-select').select2({
        language: 'ro',
    });

    $('.ro-city-select').select2({}).prop('disabled', true);

    // i18n
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

    $('.ro-region-select').on('change', function () {
        $('.ro-city-select').trigger('regionChanged');
    });

    $('.ro-city-select').on('regionChanged', function () {
        const getRegionValue = $('.ro-region-select').val();
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

                $('.ro-city-select').empty().select2({
                    data: formatResponse,
                }).prop('disabled', false);
            }
        });
    });
});
