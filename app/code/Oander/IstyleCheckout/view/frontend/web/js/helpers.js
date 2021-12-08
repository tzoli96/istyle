define([
  'ko',
  'Magento_Checkout/js/model/quote',
  'Magento_Checkout/js/checkout-data',
  'jquery',
  'Oander_IstyleCheckout/js/model/store',
  'mage/translate'
], function (ko, quote, checkoutData, $, store, $t) {
  'use strict';

  return {
    fieldsContent: {},
    interval: 500,

    /**
     * Get shipping address
     * @returns {String}
     */
    getShippingAddress: ko.computed(function () {
      var shippingAddress = quote.shippingAddress() ? quote.shippingAddress() : checkoutData.getShippingAddressFromData();
      var address = '';

      if (shippingAddress) {
        (shippingAddress.postcode !== '*'
          && (shippingAddress.postcode !== undefined && shippingAddress.postcode !== '')
          && (shippingAddress.city !== undefined && shippingAddress.city !== '')
          && (shippingAddress.street !== undefined && shippingAddress.street !== '')
          && (shippingAddress.firstname !== undefined && shippingAddress.firstname !== '')
          && (shippingAddress.lastname !== undefined && shippingAddress.lastname !== ''))
          ? address = shippingAddress.firstname + ' ' + shippingAddress.lastname + ', ' + shippingAddress.street[0] + ', ' + shippingAddress.city + ', ' + shippingAddress.postcode
          : address = $t('Please enter your shipping address.');
      }
      else {
        address = $t('Please enter your shipping address.');
      }

      return address;
    }),

    /**
     * Validate shipping fields
     * @param {HTMLElement} form
     * @returns {Void}
     */
    validateShippingFields: function (form) {
      var self = this;

      if (form.hasClass('form-shipping-address')) self.watchRequiredFields(form);

      if (form) {
        var fields = form.find('.form-group');

        fields.each(function (index, field) {
          var fieldElement = $(field).find('.form-control');

          fieldElement.on('keyup change', function () {
            self.classHandler($(this));
          });

          self.classHandler(fieldElement);
        });
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
        var fields = form.find('.form-group._required, .form-group.true');

        fields.each(function (index, field) {
          if (!$(field).attr('style')) {
            var fieldElement = $(field).find('.form-control');

            fieldElement.on('keyup change', function () {
              self.requiredHandler($(this), Number(index));
            });

            self.requiredHandler(fieldElement, Number(index));
          }
        });
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
    },

    /**
     * Step counter
     * @param {HTMLElement} step
     * @returns {Void}
     */
    stepCounter: function (step) {
      var stepData = step.attr('data-step-count');

      var lineInterval = setInterval(function () {
        if ($('.block__line').find('.line__information').length > 0) {
          $('.block__line').find('.line__information').css('width', ((stepData * 20) / 2) + '%');
          clearInterval(lineInterval);
        }
      }, this.interval);
    },

    /**
     * Shipping method visible handling
     * @param {String} method
     * @returns {Boolean}
     */
    shippingMethodVisibleHandling: function (method) {
      if (method.indexOf('warehouse') > -1) return true;
      return false;
    },

    /**
     * Are addresses equal
     * @param {Object} shipping
     * @param {Object} billing
     * @returns {Boolean}
     */
    areAddressesEqual: function (shipping, billing) {
      var requiredFields = ['firstname', 'lastname', 'postcode', 'city', 'street', 'telephone'];
      var equalFieldsCount = 0;

      if (shipping.postcode !== '*') {
        for (var field in requiredFields) {
          if (requiredFields[field] !== 'street') {
            if (shipping[requiredFields[field]] === billing[requiredFields[field]]) equalFieldsCount++;
          }
          else {
            if (Array.isArray(shipping[requiredFields[field]]) && Array.isArray(billing[requiredFields[field]])) {
              if (shipping[requiredFields[field]][0] === billing[requiredFields[field]][0]) equalFieldsCount++;
            }
          }
        }

        if (billing.company) {
          if (billing.customerAddressId) {
            return true;
          }
          else {
            return false;
          }
        }
        else {
          if (billing.customerAddressId) {
            return true;
          }
          else if (equalFieldsCount == requiredFields.length) {
            return true;
          }
          else {
            return false;
          }
        }
      }
      else {
        return true;
      }
    },

    /**
     * Has value
     * @param {String} field
     * @returns {Boolean}
     */
    hasValue: function (field) {
      if (field !== undefined && field !== '') return true;
      return false;
    },

    checkPostcodeExpressShipping: function (inputVal) {
      // Express shipping postcode check
      console.log('lefut', inputVal);
      var postalCodes = [
        '1111',
        '2177',
        '2222',
        '3333',
        '4444',
        '5555'
      ];

      if (postalCodes.indexOf(inputVal) === -1) {
        return true;
      } else {
        return false;
      }
    },
  }
});
