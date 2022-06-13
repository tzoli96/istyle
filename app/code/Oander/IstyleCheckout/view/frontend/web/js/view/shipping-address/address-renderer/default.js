/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
  'jquery',
  'ko',
  'uiComponent',
  'Magento_Checkout/js/action/select-shipping-address',
  'Magento_Checkout/js/model/quote',
  'Oander_IstyleCheckout/js/model/shipping-address/form-state',
  'Magento_Checkout/js/checkout-data',
  'Magento_Customer/js/customer-data',
  'Oander_IstyleCheckout/js/helpers',
  'Oander_IstyleCheckout/js/view/billing-address/sort',
  'Oander_IstyleCheckout/js/model/store'
], function ($, ko, Component, selectShippingAddressAction, quote, formState, checkoutData, customerData, helpers, sort, store) {
  'use strict';

  var countryData = customerData.get('directory-data');

  return Component.extend({
    defaults: {
      template: 'Magento_Checkout/shipping-address/address-renderer/default'
    },
    hasSelectedAddress: formState.hasSelectedAddress,

    initObservable: function () {
      this._super();
      this.isSelected = ko.computed(function () {
        var isSelected = false;
        var shippingAddress = quote.shippingAddress();
        if (shippingAddress) {
          isSelected = shippingAddress.getKey() == this.address().getKey();
          formState.hasSelectedAddress(true);
        }
        return isSelected;
      }, this);

      formState.isVisible.subscribe(function (value) {
        if (value) {
          this.validateShippingFields();
          var self = this;

          // Express shipping postcode fill to new address
          var currentLS = store.getLocalStorage();
          var shippingMethod = currentLS.shippingMethod;

          if (currentLS.hasOwnProperty('shippingMethod') &&
            shippingMethod.hasOwnProperty('expressShippingPostalCode') &&
            shippingMethod.expressShippingPostalCode !== '' &&
            $('.form-shipping-address input[name=postcode]').val() === '') {
              $('.form-shipping-address input[name=postcode]').val(shippingMethod.expressShippingPostalCode);
          }
        }
      }, this);
      return this;
    },

    getCountryName: function (countryId) {
      return (countryData()[countryId] != undefined) ? countryData()[countryId].name : "";
    },

    selectAddress: function () {
      selectShippingAddressAction(this.address());
      checkoutData.setSelectedShippingAddress(this.address().getKey());
      formState.isVisible(false);

      formState.hasSelectedAddress(true);
    },

    editAddress: function () {
      formState.isVisible(true);
      console.log('editaddress defaultjs')

        if ($('#new-shipping-address').length) {
            $('html, body').animate({
                scrollTop: $('#new-shipping-address').offset().top - 100
            }, 500);

            $('#shipping-new-address-form').find('.form-group').first().find('.form-control').focus();
        }
    },

    validateShippingFields: function () {
      helpers.validateShippingFields($('.form-shipping-address'));
    },

    sortCardAddress: function (address) {
      return sort.sortCardAddress(address, 'individual');
    }
  });
});
