define([
  'ko',
  'Magento_Checkout/js/model/quote',
  'jquery',
  'Oander_IstyleCheckout/js/model/store',
], function (ko, quote, $, store) {
  'use strict';

  return {
    fieldsContent: {},
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

    /**
     * Validate shipping fields
     * @param {HTMLElement} form
     * @returns {Void}
     */
    validateShippingFields: function (form) {
      var self = this;

      self.watchRequiredFields(form);

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

    /**
     * Class handler
     * @param {HTMLElement} element
     * @returns {Void}
     */
    classHandler: function (element) {
      var formGroup = $(element).closest('.form-group');

      if ($(element).length) {
        if ($(element).val().length > 0) {
          if (formGroup.find('.field-tooltip').length) formGroup.addClass('has-field-tooltip');
          formGroup.addClass('filled');
        }
        else {
          formGroup.removeClass('filled');
        }
      }
    },

    /**
     * Watch required fields
     * @param {HTMLElement} form
     * @returns {Void}
     */
    watchRequiredFields: function (form) {
      var self = this;

      if (form) {
        var fields = form.querySelectorAll('.form-group._required, .form-group.true');

        for (var field in fields) {
          if ((typeof fields[field] == 'object'
            && !fields[field].getAttribute('style'))) {
            var fieldElement = $(fields[field]).find('.form-control');

            fieldElement.on('keyup change', function () {
              self.requiredHandler($(this), Number(field));
            });

            self.requiredHandler(fieldElement, Number(field));
          }
        }
      }
    },

    /**
     * Required handler
     * @param {HTMLElement} element
     * @param {Number} index
     * @returns {Void}
     */
    requiredHandler: function (element, index) {
      var self = this;

      if ($(element).length && !isNaN(index)) {
        if ($(element).val().length > 0) {
          self.fieldsContent[index] = true;
        }
        else {
          self.fieldsContent[index] = false;
        }
      }

      self.checkRequiredFields();
    },

    /**
     * Check required fields
     * @returns {Void}
     */
    checkRequiredFields: function () {
      var fields = this.fieldsContent;
      var fieldsLength = 0;
      var validatedFieldsCount = 0;

      for (var field in fields) {
        fieldsLength++;
        if (fields[field]) validatedFieldsCount++;
      }

      if (fieldsLength === validatedFieldsCount) {
        store.shippingAddress.continueBtn(true);
      }
      else {
        store.shippingAddress.continueBtn(false);
      }
    }
  }
});
