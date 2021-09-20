define([
  'jquery',
  'Magento_Checkout/js/model/quote',
  'mage/translate'
], function ($, quote, $t) {
  'use strict';

  var fancourier = {
    region: '',
    city: '',
    regions: [],

    fanCourierInit: function () {
      var self = this;
      var checkExist = setInterval(function () {
        if ($('.field[name="shippingAddress.region"] .control .select.form-control').length) {
          self.region = $('.field[name="shippingAddress.region"]');
          self.city = $('.field[name="shippingAddress.city"]');

          self.init();
          clearInterval(checkExist);
        }
      }, 100);
    },

    init: function () {
      if (this.region.length > 0) {
        this.hideDefaultInputs();
        this.appendNewInputs();
        this.appendRegions();

        this.dropdownRegionEvents();
        this.regionSearch();
      }
    },

    hideDefaultInputs: function () {
      this.region.find('.control').hide();
    },

    appendNewInputs: function () {
      var selectedRegion = '';
      var selectedCity = '';
      var cityControl = this.city.find('.control');
      var cityInput = cityControl.find('input[name="city"]');
      var regionSelect = this.region.find('.control').find('.select.form-control');

      if (quote.shippingAddress()) {
        if (quote.shippingAddress().city != null) {
          selectedCity = quote.shippingAddress().city;
        }
      }

      if (regionSelect.find('option[selected="selected"]').length > 0) {
        selectedRegion = regionSelect.find('option:selected').val();
      }

      if (cityInput.attr('value') != '') {
        selectedRegion = regionSelect.find('option:selected').val();
      }

      this.region.append('<div class="dropdown dropdown--fc"><div class="dropdown__input"><input type="text" value="' + selectedRegion + '" placeholder="' + $t('State/Province') + '" name="fc-search" class="dropdown__search form-control" autocomplete="off"></div><div class="dropdown__list"></div></div>');
      cityInput.attr('disabled', 'disabled');
      cityInput.addClass('dropdown__search');
      cityInput.attr('placeholder', $t('City'));
      cityInput.wrap('<div class="dropdown dropdown--fc"></div>');
      this.city.find('.dropdown--fc').append('<div class="dropdown__list"></div>');
    },

    appendRegions: function () {
      var self = this;
      var regionOptions = this.region.find('.control .select.form-control option');

      regionOptions.each(function () {
        self.regions.push($(this).val());
        self.region.find('.dropdown--fc').find('.dropdown__list').append('<div class="dropdown__item">' + $(this).val() + '</div>');
      });
    },

    dropdownRegionEvents: function () {
      var self = this;
      var selector = this.region;
      var regionSelect = selector.find('.control').find('.select.form-control');
      var regionDropdownSearch = selector.find('.dropdown__search');
      var regionDropdownList = selector.find('.dropdown__list');
      var regionDropdownItem = selector.find('.dropdown__item');

      var cityDropdownSearch = this.city.find('.dropdown__search');
      var cityDropdownList = this.city.find('.dropdown__list');
      var cityInput = this.city.find('input[name="city"]');

      var getCitiesAjaxUrl = window.location.protocol + '//' + window.location.hostname + '/fan_courier_validator/Address/GetCities/';

      regionDropdownSearch.click(function () {
        regionDropdownList.show();
        $(this).addClass('active');
      });

      regionDropdownItem.click(function () {
        regionDropdownItem.removeClass('active');
        $(this).addClass('active');

        regionDropdownSearch.val($(this).text());
        regionDropdownSearch.removeClass('active');
        regionDropdownList.hide();

        cityDropdownSearch.attr('value', '');
        cityDropdownSearch.removeAttr('disabled');

        regionSelect.find('option').removeAttr('selected');
        regionSelect.find('option[value="' + $(this).text() + '"]').attr('selected', 'selected');
        regionSelect.find('option[value="' + $(this).text() + '"]').trigger('change');

        var activeRegion = regionSelect.find('option:selected').val();

        if (xhr && xhr.readyState != null) {
          xhr.abort();
        }

        var xhr = $.ajax({
          showLoader: true,
          url: getCitiesAjaxUrl,
          data: {
            state: activeRegion
          },
          type: 'GET',
          dataType: 'json'
        }).done(function (data) {
          cityDropdownList.html('');
          cityInput.removeAttr('disabled');

          self.dropdownCityEvents(data.cities);
          self.citySearch(data.cities);
        });
      });

      $('body').on('click', function () {
        if (!regionDropdownSearch.is(':focus')) {
          regionDropdownSearch.removeClass('active');
          regionDropdownList.hide();
        }
      });
    },

    regionSearch: function () {
      var self = this;
      var selector = this.region;
      var regionDropdownSearch = selector.find('.dropdown__search');
      var regionDropdownList = selector.find('.dropdown__list');

      regionDropdownSearch.on('keyup', function () {
        var searchValue = $(this).val();

        selector.closest('.field.form-group').removeClass('fc-error');
        $('.field.region-town-error').remove();

        regionDropdownList.html('');

        for (var index = 0; index < self.regions.length; index++) {
          var element = self.regions[index];

          if (element.indexOf(searchValue) > -1 || element.toLowerCase().indexOf(searchValue) > -1 || element.toUpperCase().indexOf(searchValue) > -1) {
            regionDropdownList.append('<div class="dropdown__item">' + element + '</div>');
          }
        }

        self.dropdownRegionEvents();
      });

      regionDropdownSearch.on('change', function () {
        selector.closest('.field.form-group').removeClass('fc-error');
        $('.field.region-town-error').remove();
      });
    },

    dropdownCityEvents: function (cities) {
      var selector = this.city;
      var currentCities = cities;
      var cityDropdownSearch = selector.find('.dropdown__search');
      var cityDropdownList = selector.find('.dropdown__list');
      var cityDropdownItem = selector.find('.dropdown__item');

      for (var index = 0; index < currentCities.length; index++) {
        var element = currentCities[index];

        cityDropdownList.append('<div class="dropdown__item">' + element + '</div>');
      }

      cityDropdownSearch.click(function () {
        cityDropdownList.show();
        $(this).addClass('active');
      });

      selector.find('.dropdown__item').click(function () {
        cityDropdownItem.removeClass('active');
        $(this).addClass('active');

        cityDropdownSearch.val($(this).text());
        cityDropdownSearch.removeClass('active');
        cityDropdownList.hide();

        cityDropdownSearch.trigger('change');
      });

      $('body').on('click', function () {
        if (!cityDropdownSearch.is(':focus')) {
          cityDropdownSearch.removeClass('active');
          cityDropdownList.hide();
        }
      });
    },

    citySearch: function (recentCities) {
      var selector = this.city;
      var cities = recentCities;
      var cityDropdownSearch = selector.find('.dropdown__search');
      var cityDropdownList = selector.find('.dropdown__list');
      var cityDropdownItem = selector.find('.dropdown__item');

      cityDropdownSearch.on('keyup', function () {
        var searchValue = $(this).val();

        selector.closest('.field.form-group').removeClass('fc-error');
        $('.field.region-town-error').remove();

        cityDropdownList.html('');

        for (var index = 0; index < cities.length; index++) {
          var element = cities[index];

          if (element.indexOf(searchValue) > -1 || element.toLowerCase().indexOf(searchValue) > -1 || element.toUpperCase().indexOf(searchValue) > -1) {
            cityDropdownList.append('<div class="dropdown__item">' + element + '</div>');
          }
        }

        selector.find('.dropdown__item').click(function () {
          cityDropdownItem.removeClass('active');
          $(this).addClass('active');

          cityDropdownSearch.val($(this).text());
          cityDropdownSearch.removeClass('active');
          cityDropdownList.hide();
        });
      });

      cityDropdownSearch.on('change', function () {
        selector.closest('.field.form-group').removeClass('fc-error');
        $('.field.region-town-error').remove();
      });
    },

    validateStateCity: function () {
      if ($('.field[name="shippingAddress.region"]').find('.select.form-control').length) {
        var region = $('.field[name="shippingAddress.region"]');
        var city = $('.field[name="shippingAddress.city"]');

        var selectedRegion = region.find('.dropdown__search').val();
        var selectedCity = city.find('input[name="city"]').val();
        var regionSelect = region.find('.control').find('.select.form-control');
        var validateStateCityAjaxUrl = window.location.protocol + '//' + window.location.hostname + '/fan_courier_validator/Address/ValidateStateCity/';

        regionSelect.find('option').removeAttr('selected');
        regionSelect.append('<option value="' + selectedRegion + '" selected="selected">' + selectedRegion + '</div>');
        regionSelect.find('option:selected').trigger('change');

        region.find('.dropdown__search').trigger('change');
        city.find('input[name="city"]').trigger('change');

        selectedRegion = region.find('.dropdown__search').val();
        selectedCity = city.find('input[name="city"]').val();

        if (xhr && xhr.readyState != null) {
          xhr.abort();
        }

        var xhr = $.ajax({
          showLoader: true,
          url: validateStateCityAjaxUrl,
          data: {
            state: selectedRegion,
            city: selectedCity
          },
          type: 'GET',
          dataType: 'json'
        }).done(function (data) {
          var response = data.isVaild;

          if (response == false) {
            $('.field.region-town-error').remove();
            region.addClass('fc-error');
            city.addClass('fc-error');
            city.after('<div class="field region-town-error">' + $t('Invalid region-city.') + '</div>');

            $('html, body').animate({
              scrollTop: $(".field.region-town-error").offset().top - 350
            }, 2000);
          }
          else {
            region.removeClass('fc-error');
            city.removeClass('fc-error');
            $('.field.region-town-error').remove();
          }
        });
      }
    }
  }

  return fancourier;
});
