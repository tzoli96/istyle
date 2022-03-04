define([
    'jquery',
    'select2',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'mage/translate'
], function ($, select2, domObserver, $t) {
    'use strict';

    // init selects
    $("#ro-region").select2({
        language: "ro",
        placeholder: "select region placeholder",
    });

    $("#ro-city").select2({}).prop("disabled", true);

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
    $("select:not([ajax-url])").select2({});

    // on region change trigger city select
    $("#ro-region").on('change', function () {
        $("#ro-city").trigger('regionChanged');
    });

    // activate city select when region selected
    $("#ro-city").on('regionChanged', function () {
        $("#ro-city").prop("disabled", false);

        // use for req
        const getRegionValue = $("#ro-region").val();

        const getCountiesAjaxUrl = 'https://reqres.in/api/unknown';
        // const getCountiesAjaxUrl = '<?php echo $block->getParentBlock()->getCitiesAjaxUrlParam(); ?>';

        $.ajax({
            url: getCountiesAjaxUrl,
            type: 'GET',
            dataType: 'json'
        }).done(function (data) {
            const response = data.data;
            const formatResponse = [];

            if (response) {
                //success
                $.each(response, function (index, value) {
                    console.log('each', value)
                    formatResponse.push({
                        id: value.id - 1,
                        text: value.name
                    });
                });

                $(".js-data-example-ajax").select2({
                    placeholder: "Select a region first",
                    //minimumInputLength: 1,
                    data: formatResponse,
                })
            } else {
                //error state, no data
            }
        });
    });
});
