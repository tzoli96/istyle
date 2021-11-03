define([
  'jquery',
  'Oander_IstyleCheckout/js/model/store',
  'Oander_IstyleCheckout/js/view/billing-address/store',
], function ($, store, billingAddressStore) {
  'use strict';

  var validate = {
    mainFields: {},

    /**
     * Check validated fields
     * @param {HTMLElement} form
     * @returns {Void}
     */
    checkValidatedFields: function (form) {
      var self = this;

      if (form) {
        var fields = form.find('.form-group._required, .form-group.true');

        fields.each(function (index, field) {
          if (self.isVisibleInDom($(field))) {
            var fieldElement = $(field).find('.form-control');

            fieldElement.on('keyup change', function () {
              self.requiredHandler($(this), fieldElement.attr('name'));
            });

            self.requiredHandler(fieldElement, fieldElement.attr('name'));
          }
        });
      }
    },

    /**
     * Required handler
     * @param {HTMLElement} element
     * @param {String} key
     * @returns {Void}
     */
    requiredHandler: function (element, key) {
      var self = this;

      if ($(element).length) {
        if (self.isVisibleInDom($(element).closest('.form-group'))) {
          delete self.mainFields[key];
          if ($(element).val().length > 0) self.mainFields[key] = true;
          else self.mainFields[key] = false;
        }
      }

      billingAddressStore.fieldsContent(this.mainFields);

      this.checkRequiredFields();
    },

    /**
     * Check required fields
     * @returns {Void}
     */
    checkRequiredFields: function () {
      var fields = billingAddressStore.fieldsContent();
      var fieldsLength = 0;
      var validatedFieldsCount = 0;

      for (var field in fields) {
        fieldsLength++;
        if (fields[field]) validatedFieldsCount++;
      }

      if (fieldsLength === validatedFieldsCount) store.billingAddress.continueBtn(true);
      else store.billingAddress.continueBtn(false);
    },

    /**
     * Is visible in DOM
     * @param {HTMLElement} elem
     * @returns {Boolean}
     */
    isVisibleInDom: function (elem) {
      var style = elem.attr('style');
      if (style) {
        if (style.indexOf('display: none') > -1) return false;
        return true;
      }
      return true;
    }
  };

  return validate;
});
