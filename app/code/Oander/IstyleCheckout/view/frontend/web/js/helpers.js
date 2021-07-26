define([
  'ko',
  'Magento_Checkout/js/model/quote',
  'jquery'
], function (ko, quote, $) {
  'use strict';

  return {
    /**
     * Get shipping address
     * @returns {String}
     */
     getShippingAddress: ko.computed(function () {
      var shippingAddress = quote.shippingAddress();
      var address = '';

      if (shippingAddress) {
        (shippingAddress.postcode !== '*'
        && (shippingAddress.postcode !== undefined && shippingAddress.postcode !== '')
        && (shippingAddress.city !== undefined && shippingAddress.city !== '')
        && (shippingAddress.street !== undefined && shippingAddress.street !== '')
        && (shippingAddress.firstname !== undefined && shippingAddress.firstname !== '')
        && (shippingAddress.lastname !== undefined && shippingAddress.lastname !== ''))
        ? address = shippingAddress.firstname + ' ' + shippingAddress.lastname + ', ' + shippingAddress.street[0] + ', ' + shippingAddress.city + ', ' + shippingAddress.postcode
        : address = false;
      }
      else {
        address = false;
      }

      return address;
    }),

    /**
     * Is filled
     * @param {Object<String> | String} value
     * @returns String
     */
    isFilled: function (value) {
      if (value) return 'filled';
      return '';
    },

    validateShippingFields: function (form) {
      var self = this;

      if (form) {
        var fields = form.querySelectorAll('.form-group');

        for (var field in fields) {
          if (typeof fields[field] == 'object') {
            var fieldElement = $(fields[field]).find('.form-control');

            fieldElement.on('keyup change', function () {
              self.classHandler($(this));
            });

            self.classHandler(fieldElement);
          }
        }
      }
    },

    classHandler: function (element) {
      var formGroup = $(element).closest('.form-group');

      if ($(element).val().length > 0) {
        if (formGroup.find('.field-tooltip').length) formGroup.addClass('has-field-tooltip');
        formGroup.addClass('filled');
      }
      else {
        formGroup.removeClass('filled');
      }
    }
  }
});
