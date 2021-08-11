define([
  'jquery',
  'ko',
  'mage/translate',
  'Magento_Customer/js/model/customer',
  'Magento_Checkout/js/model/quote',
  'Magento_Checkout/js/action/select-billing-address',
  'Oander_IstyleCheckout/js/helpers',
  'Magento_Checkout/js/checkout-data',
  'Oander_IstyleCheckout/js/model/store',
], function ($, ko, $t, customer, quote, selectBillingAddress, helpers, checkoutData, store) {
  'use strict';

  var mixin = {
    fieldsContent: {},

    selectedBillingAddress: store.billingAddress.selectedBillingAddress,
    hasSelectedAddress: store.billingAddress.hasSelectedAddress,
    newAddress: store.billingAddress.newAddress,
    hasNewAddress: store.billingAddress.hasNewAddress,
    tabSelector: store.billingAddress.tabSelector,
    formIsVisible: store.billingAddress.formIsVisible,
    continueBtn: store.billingAddress.continueBtn,

    getBillingAddress: function () {
      var billingAddress = quote.billingAddress();
      var address = '';

      if (billingAddress) {
        if (billingAddress.postcode !== '*'
        && (billingAddress.postcode !== undefined && billingAddress.postcode !== '')
        && (billingAddress.city !== undefined && billingAddress.city !== '')
        && (billingAddress.street !== undefined && billingAddress.street !== '')
        && (billingAddress.firstname !== undefined && billingAddress.firstname !== '')
        && (billingAddress.lastname !== undefined && billingAddress.lastname !== '')) {
          address = billingAddress.firstname + ' ' + billingAddress.lastname + ', ' + billingAddress.street[0] + ', ' + billingAddress.city + ', ' + billingAddress.postcode;

          if ((billingAddress.company !== undefined && billingAddress.company !== '') && (billingAddress.vatId !== undefined && billingAddress.vatId !== '')) {
            address = billingAddress.company + ', ' + billingAddress.street[0] + ', ' + billingAddress.city + ', ' + billingAddress.postcode;
          }
        }
        else {
          address = false;
        }
      }
      else {
        address = false;
      }

      return address;
    },

    /**
     * Form changes
     * @returns {Void}
     */
    formChanges: function () {
      var self = this;
      var formElements = this.formElements();

      this.tabs();

      $('.block--billing-address .card__action').on('click', function () {
        if (!store.billingAddress.hasNewAddress()) {
          self.setBillingAddress(quote.shippingAddress());
          $(formElements.tabs).find('.tab__switch[data-tab="billing-person"]').trigger('click');
        }
        else {
          self.setBillingAddress(store.billingAddress.newAddress());
        }

        helpers.validateShippingFields(document.querySelector('.form--billing-address'));
        self.checkValidatedFields(document.querySelector('.form--billing-address'));
      });

      this.setBillingAddress(quote.shippingAddress());

      quote.shippingAddress.subscribe(function (address) {
        if (!store.billingAddress.hasNewAddress()) {
          this.setBillingAddress(address);
        }
        else {
          this.setBillingAddress(store.billingAddress.newAddress());
        }
      }, this);

      quote.billingAddress.subscribe(function (address) {
        var selectedBillingAddress = {
          id: address.customerAddressId ? address.customerAddressId : false,
          status: address.customerAddressId ? 'exist' : 'new',
          isCompany: address.company ? true : false,
          address: address,
        };

        store.billingAddress.selectedBillingAddress(selectedBillingAddress);

        if (!address.customerAddressId) {
          store.billingAddress.hasNewAddress(true);
          store.billingAddress.newAddress(address);
        }

        store.billingAddress.hasSelectedAddress(true);
      }, this);

      store.billingAddress.formIsVisible.subscribe(function (value) {
        if (value) {
          if (!store.billingAddress.hasNewAddress()) {
            this.setBillingAddress(quote.shippingAddress());
          }
          else {
            this.setBillingAddress(store.billingAddress.newAddress());
          }

          console.log('form is visible');

          if (store.billingAddress.selectedBillingAddress().isCompany) {
            $(formElements.tabs).find('.tab__switch[data-tab="billing-company"]').trigger('click');
            $(formElements.companyField).find('.form-control').focus();
          }
          else {
            $(formElements.tabs).find('.tab__switch[data-tab="billing-person"]').trigger('click');
            $(formElements.form).find('.form-group').first().find('.form-control').focus();
          }
        }
      }, this);
    },

    /**
     * Form elements
     * @returns {Object}
     */
    formElements: function () {
      var form = document.querySelector('.form.form--billing-address');
      var tabs = document.querySelector('.tab.tab--billing-address');
      var titles = tabs.querySelectorAll('.tab__switch');
      var companyField = form.querySelector('[name="billingAddressshared.company"]');
      var vatIdField = form.querySelector('[name="billingAddressshared.vat_id"]');

      return {
        form: form,
        tabs: tabs,
        titles: titles,
        companyField: companyField,
        vatIdField: vatIdField,
      }
    },

    /**
     * Tabs
     * @returns {Void}
     */
    tabs: function () {
      var self = this;
      var formElements = this.formElements();

      Array.prototype.forEach.call(formElements.titles, function (title) {
        var formId = title.getAttribute('data-tab');
        var isActive = title.parentNode.classList.contains('active');

        title.addEventListener('click', function () {
          Array.prototype.forEach.call(formElements.titles, function (tabTitle) {
            tabTitle.parentNode.classList.remove('active');
          });

          title.parentNode.classList.add('active');
          formElements.form.setAttribute('data-tab', formId);
          self.formTransform(formId);

          self.checkValidatedFields(document.querySelector('.form--billing-address'));
        });

        if (isActive) self.watchSpecificFields(formId);
      });
    },

    /**
     * Watch specific fields
     * @returns {Void}
     */
    watchSpecificFields: function (formId) {
      var self = this;

      var watch = setInterval(function () {
        var formElements = self.formElements();
        if (formElements.companyField && formElements.vatIdField) {
          self.formTransform(formId);
          helpers.validateShippingFields(document.querySelector('.form--billing-address'));
          self.checkValidatedFields(document.querySelector('.form--billing-address'));
          clearInterval(watch);
        }
      }, 1000);
    },

    /**
     * Form transform
     * @param {String} formId
     * @returns {Void}
     */
    formTransform: function (formId) {
      switch (formId) {
        case 'billing-person':
          this.formPerson();
          break;
        case 'billing-company':
          this.formCompany();
          break;
      }
    },

    /**
     * Form person
     * @returns {Void}
     */
    formPerson: function () {
      var formElements = this.formElements();
      var names = {
        firstname: 'Firstname',
        lastname: 'Lastname',
      };

      $(formElements.companyField).hide();
      $(formElements.companyField).removeClass('_required');
      $(formElements.vatIdField).hide();
      $(formElements.vatIdField).removeClass('_required');

      $(formElements.form).find('[name="billingAddressshared.firstname"] > .label').text($t(names.firstname));
      $(formElements.form).find('[name="billingAddressshared.lastname"] > .label').text($t(names.lastname));
    },

    /**
     * Form company
     * @returns {Void}
     */
    formCompany: function () {
      var formElements = this.formElements();
      var names = {
        firstname: 'Contact person firstname',
        lastname: 'Contact person lastname',
      };

      $(formElements.companyField).show();
      $(formElements.companyField).addClass('_required');
      $(formElements.vatIdField).show();
      $(formElements.vatIdField).addClass('_required');

      $(formElements.form).find('[name="billingAddressshared.firstname"] > .label').text($t(names.firstname));
      $(formElements.form).find('[name="billingAddressshared.lastname"] > .label').text($t(names.lastname));

      $(formElements.companyField).find('.form-control').focus();

      this.fieldErrorHandling($(formElements.companyField));
      this.fieldErrorHandling($(formElements.vatIdField));
    },

    /**
     * Field error handling
     * @returns {Void}
     */
    fieldErrorHandling: function (field) {
      if (!field.find('.mage-error').length) {
        field.append('<div class="mage-error d-none">' + $t('Required fields') + '</div>');
      }

      field.find('.form-control').on('keyup', function () {
        if (!$(this).val().length) {
          field.addClass('_error');
          field.find('.mage-error').removeClass('d-none');
        }
        else {
          field.removeClass('_error');
          field.find('.mage-error').addClass('d-none');
        }
      });
    },

    /**
     * Watch field
     * @returns {Void}
     */
    watchField: function (field) {
      if (!field.find('.form-control').val().length) {
        field.addClass('_error');
        field.find('.mage-error').removeClass('d-none');
        return false;
      }
      else {
        field.removeClass('_error');
        field.find('.mage-error').addClass('d-none');
        return true;
      }
    },

    /**
     * Check validated fields
     * @returns {Void}
     */
    checkValidatedFields: function (form) {
      var self = this;

      if (form) {
        var fields = form.querySelectorAll('.form-group._required, .form-group.true');

        Array.prototype.forEach.call(fields, function (field, index) {
          if (field.getAttribute('style') != 'display: none;') {
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
        store.billingAddress.continueBtn(true);
      }
      else {
        store.billingAddress.continueBtn(false);
      }
    },

    /**
     * Set billing address
     * @returns {Void}
     */
    setBillingAddress: function (address) {
      var formElements = this.formElements();

      for (var item in address) {
        var elem = formElements.form.querySelector('[name="' + item + '"]');
        var value = address[item];

        if (item == 'street') {
          elem = formElements.form.querySelector('[name="' + item + '[0]"]');
        }

        if (item == 'vatId') {
          elem = formElements.form.querySelector('[name="vat_id"]');
        }

        if (value !== undefined && value !== null) {
          if (elem) {
            elem.value = value;
            elem.dispatchEvent(new Event('change'));
          }
        }
      }
    },

    /**
     * Is selected by address id
     * @returns {Boolean}
     */
    isSelectedByAddressId: function (addressId) {
      if ((this.selectedBillingAddress().id == addressId) && this.hasSelectedAddress()) return true;
      return false;
    },

    /**
     * New address
     * @returns {Void}
     */
    addNewAddress: function () {
      store.billingAddress.hasSelectedAddress(false);
      store.billingAddress.formIsVisible(true);
      this.scrollToForm($('#billing-new-address-form'));
    },

    selectBillingAddressItem: function (address) {
      var selectAddress = address;

      if (address == 'new') {
        selectAddress = store.billingAddress.newAddress();
      }

      selectBillingAddress(selectAddress);
      store.billingAddress.hasSelectedAddress(true);
      store.billingAddress.formIsVisible(false);
    },

    scrollToForm: function (formElement) {
      if (formElement.length) {
        $('html, body').animate({
          scrollTop: formElement.offset().top - 100
        }, 500);
      }
    },

    /**
     * Billing continue
     * @returns {Void}
     */
    billingContinue: function () {
      var formElements = this.formElements();
      var activeTab = $(formElements.tabs).find('.tab__title.active').find('.tab__switch').attr('data-tab');

      if (store.billingAddress.continueBtn()) {
        if (activeTab == 'billing-company') {
          if (this.watchField($(formElements.companyField)) && this.watchField($(formElements.vatIdField))) {
            this.updateAddress();
            store.billingAddress.formIsVisible(false);
          }
        }
        else {
          $(formElements.companyField).find('.form-control').val('').trigger('change');
          $(formElements.vatIdField).find('.form-control').val('').trigger('change');

          this.updateAddress();
          store.billingAddress.formIsVisible(false);

          $(formElements.tabs).find('.tab__title.active .tab__switch[data-tab="billing-person"]').trigger('click');
        }

        $('.block--payment-method').find('.card__action').trigger('click');
      }
      else {
        if (activeTab == 'billing-company') {
          this.watchField($(formElements.companyField));
          this.watchField($(formElements.vatIdField));
        }
      }
    },

    selectedBillingContinue: function () {
      checkoutData.setSelectedBillingAddress(quote.billingAddress().getKey());
      $('.block--payment-method').find('.card__action').trigger('click');
    },
  };

  return function (target) {
    return target.extend(mixin);
  };
});
