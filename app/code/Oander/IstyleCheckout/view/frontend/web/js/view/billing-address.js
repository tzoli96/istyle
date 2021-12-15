define([
  'jquery',
  'ko',
  'mage/translate',
  'Magento_Customer/js/model/customer',
  'Magento_Customer/js/model/address-list',
  'Magento_Checkout/js/model/quote',
  'Magento_Checkout/js/action/select-billing-address',
  'Oander_IstyleCheckout/js/helpers',
  'Magento_Checkout/js/checkout-data',
  'Magento_Checkout/js/action/get-payment-information',
  'Oander_IstyleCheckout/js/model/store',
  'Oander_IstyleCheckout/js/view/billing-address/store',
  'Oander_IstyleCheckout/js/view/billing-address/validate',
  'Oander_IstyleCheckout/js/view/billing-address/base',
  'Oander_IstyleCheckout/js/view/billing-address/sort',
], function (
  $,
  ko,
  $t,
  customer,
  addressList,
  quote,
  selectBillingAddress,
  helpers,
  checkoutData,
  getPaymentInformationAction,
  store,
  billingAddressStore,
  billingAddressValidate,
  billingAddressBase,
  billingAddressSort) {
  'use strict';

  var mixin = {
    selectedBillingAddress: store.billingAddress.selectedBillingAddress,
    hasSelectedAddress: store.billingAddress.hasSelectedAddress,
    newAddress: store.billingAddress.newAddress,
    hasNewAddress: store.billingAddress.hasNewAddress,
    tabSelector: store.billingAddress.tabSelector,
    formIsVisible: store.billingAddress.formIsVisible,
    continueBtn: store.billingAddress.continueBtn,
    userSelectBillingAddress: store.billingAddress.userSelectBillingAddress,

    isBillingAddressVisible: ko.observable(false),

    addressOptions: addressList().filter(function (address) {
      return address.getType() == 'customer-address';
    }),

    /**
     * Get billing address
     * @returns {String}
     */
    getBillingAddress: ko.computed(function () {
      var billingAddress = quote.billingAddress();
      var address = '';

      if (billingAddress) {
        if (helpers.hasValue(billingAddress.postcode)
          && helpers.hasValue(billingAddress.city)
          && helpers.hasValue(billingAddress.street)
          && helpers.hasValue(billingAddress.firstname)
          && helpers.hasValue(billingAddress.lastname)) {
          address = billingAddress.firstname + ' ' + billingAddress.lastname + ', ' +
            billingAddress.street[0] + ', ' + billingAddress.city + ', ' + billingAddress.postcode;

          if (helpers.hasValue(billingAddress.company)
            || helpers.hasValue(billingAddress.vatId)) {
            address = billingAddress.company + ', ' + billingAddress.street[0] + ', ' +
              billingAddress.city + ', ' + billingAddress.postcode;
          }
        }
        else {
          address = $t('Please enter your billing address.');
        }
      }
      else {
        address = $t('Please enter your billing address.');
      }

      return address;
    }),

    /**
     * Is active
     * @param {String} step
     * @returns {Boolean}
     */
    isActive: function (step) {
      var currentLS = store.getLocalStorage();

      if (currentLS.steps && (currentLS.steps.active === step)) {
        var deferred = $.Deferred();
        getPaymentInformationAction(deferred);
        helpers.stepCounter($('[data-step="' + step + '"]'));

        return true;
      }
      else {
        return false;
      }
    },

    /**
     * Load default address
     * @returns {Void}
     */
    loadDefaultAddress: function () {
      var self = this;
      var currentLS = store.getLocalStorage();

      var addressInterval = setInterval(function () {
        if (document.querySelector('.form--billing-address')) {
          if (!currentLS.billingAddress) {
            if (!store.billingAddress.hasNewAddress()) {
              self.setBillingAddress(quote.shippingAddress());
            }
            else {
              self.setBillingAddress(store.billingAddress.newAddress());
            }
          }
          else {
            if (!currentLS.billingAddress.hasNewAddress) {
              self.setBillingAddress(quote.shippingAddress());
            }
            else {
              self.setBillingAddress(currentLS.billingAddress.newAddress);
            }
          }

          helpers.validateShippingFields($('.form--billing-address'));
          billingAddressValidate.checkValidatedFields($('.form--billing-address'));

          clearInterval(addressInterval);
        }
      }, helpers.interval);
    },

    checkUserBilling: function () {
      var currentLS = store.getLocalStorage();

      if (customer.isLoggedIn()) {
        if (store.billingAddress.userSelectBillingAddress() || currentLS.billingAddress.userSelectBillingAddress) {
          return true;
        }
        else if (store.billingAddress.formIsVisible()) {
          return true;
        }
        else {
          if (helpers.shippingMethodVisibleHandling(store.shippingMethod.selectedCode())) {
            return false;
          }
          else {
            return true;
          }
        }
      }
      else {
        return true;
      }
    },

    /**
     * Form changes
     * @returns {Void}
     */
    formChanges: function () {
      var self = this;
      var formElements = this.formElements();
      var currentLS = store.getLocalStorage();

      this.tabs();

      self.loadDefaultAddress();

      $('.block--billing-address .card__action').on('click', function () {
        self.loadDefaultAddress();
      });

      quote.shippingAddress.subscribe(function (address) {
        if (!store.getLocalStorage().billingAddress) {
          if (!store.billingAddress.hasNewAddress()) {
            this.setBillingAddress(address);
          }
          else {
            this.setBillingAddress(store.billingAddress.newAddress());
          }
        }
        else {
          if (!store.getLocalStorage().billingAddress.hasNewAddress) {
            this.setBillingAddress(address);
          }
          else {
            this.setBillingAddress(store.getLocalStorage().billingAddress.newAddress);
          }
        }
      }, this);

      var regionInterval = setInterval(function () {
        if ($('.form-group[name="billingAddress.region"] .form-control').length) {
          var region = $('.form-group[name="billingAddress.region"] .form-control');
          region.on('keyup', function () {
            store.billingAddress.region($('.form-group[name="billingAddress.region"] .form-control').val());
          });
          clearInterval(regionInterval);
        }
      }, 500);

      store.billingAddress.region.subscribe(function (value) {
        if (quote.billingAddress()) {
          quote.billingAddress().region = value;
        }
      });

      quote.billingAddress.subscribe(function (address) {
        if (store.billingAddress.formIsVisible()) store.billingAddress.userSelectBillingAddress(true);

        if (address) {
          if (address.postcode
            && this.checkUserBilling()) {
            if (address.region) {
              store.billingAddress.region(address.region);
              quote.billingAddress().region = address.region;
            }
            else {
              if (store.billingAddress.region()) {
                quote.billingAddress().region = store.billingAddress.region();
              }

              if (currentLS.billingAddress) {
                if (currentLS.billingAddress.region) {
                  quote.billingAddress().region = currentLS.billingAddress.region;
                }
              }
            }

            var selectedBillingAddress = {
              id: address.customerAddressId ? address.customerAddressId : false,
              status: address.customerAddressId ? 'exist' : 'new',
              isCompany: address.company ? true : false,
              address: address,
            };

            store.billingAddress.selectedBillingAddress(selectedBillingAddress);

            if (!address.customerAddressId) {
              address.saveInAddressBook = 1;
              store.billingAddress.hasNewAddress(true);
              store.billingAddress.newAddress(address);
            }

            store.billingAddress.hasSelectedAddress(true);
          }
          else {
            if (address !== null) quote.billingAddress(null);
          }
        }
        else {
          if (address !== null) quote.billingAddress(null);
        }
      }, this);

      store.billingAddress.formIsVisible.subscribe(function (value) {
        if (value) {
          if (!store.getLocalStorage().billingAddress) {
            if (!store.billingAddress.hasNewAddress()) {
              this.setBillingAddress(quote.shippingAddress());
            }
            else {
              this.setBillingAddress(store.billingAddress.newAddress());
            }
          }
          else {
            if (!store.getLocalStorage().billingAddress.hasNewAddress) {
              this.setBillingAddress(quote.shippingAddress());
            }
            else {
              this.setBillingAddress(store.getLocalStorage().billingAddress.newAddress);
            }
          }

          if (store.getLocalStorage().billingAddress.selectedBillingAddress
            ? store.getLocalStorage().billingAddress.selectedBillingAddress.isCompany
            : store.billingAddress.selectedBillingAddress().isCompany) {
            $(formElements.tabs).find('.tab__switch[data-tab="billing-company"]').trigger('click');
          }
          else {
            $(formElements.tabs).find('.tab__switch[data-tab="billing-person"]').trigger('click');
            $(formElements.form).find('.form-group').first().find('.form-control').focus();
          }
        }
      }, this);

      // Billing address
      if (store.steps.billingAddress() === true || currentLS.steps.billingAddress) {
        this.isBillingAddressVisible(true);
        if (store.steps.visible.indexOf('billingAddress') < 0) {
					store.steps.visible.push('billingAddress');
				}
      }

      store.steps.billingAddress.subscribe(function (value) {
        if (value === true) {
          this.isBillingAddressVisible(true);
          if (store.steps.visible.indexOf('billingAddress') < 0) {
            store.steps.visible.push('billingAddress');
          }
        }
      }, this);

      if (store.steps.active() === 'billingAddress' || currentLS.steps.active === 'billingAddress') {
        this.isBillingAddressVisible(true);
        if (store.steps.visible.indexOf('billingAddress') < 0) {
          store.steps.visible.push('billingAddress');
        }
      }
      store.steps.active.subscribe(function (value) {
        if (value === 'billingAddress') {
          this.isBillingAddressVisible(true);
          if (store.steps.visible.indexOf('billingAddress') < 0) {
            store.steps.visible.push('billingAddress');
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
      var pfpjField = form.querySelector('[name="billingAddressshared.custom_attributes.pfpj_reg_no"]');

      return {
        form: form,
        tabs: tabs,
        titles: titles,
        companyField: companyField,
        vatIdField: vatIdField,
        pfpjField: pfpjField,
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

          billingAddressValidate.checkValidatedFields($('.form--billing-address'));
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
        if (formElements.companyField || formElements.vatIdField) {
          self.formTransform(formId);
          helpers.validateShippingFields($('.form--billing-address'));
          billingAddressValidate.checkValidatedFields($('.form--billing-address'));
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
      billingAddressStore.fieldsContent({});
      billingAddressValidate.mainFields = {};

      switch (formId) {
        case 'billing-person':
          this.formPerson();
          break;
        case 'billing-company':
          this.formCompany();
          break;
      }

      billingAddressSort.sortFields(formId);
    },

    /**
     * Form person
     * @returns {Void}
     */
    formPerson: function () {
      var formElements = this.formElements();

      $(formElements.companyField).hide();
      $(formElements.companyField).removeClass('_required');
      $(formElements.vatIdField).hide();
      if ($(formElements.vatIdField).hasClass('vat-required')) $(formElements.vatIdField).removeClass('_required');

      if ($(formElements.pfpjField).length) {
        $(formElements.pfpjField).hide();
        $(formElements.pfpjField).removeClass('_required');
      }

      $(formElements.form).find('[name="billingAddressshared.firstname"] > .label').text($t('First Name'));
      $(formElements.form).find('[name="billingAddressshared.lastname"] > .label').text($t('Last Name'));
    },

    /**
     * Form company
     * @returns {Void}
     */
    formCompany: function () {
      var formElements = this.formElements();

      $(formElements.companyField).show();
      $(formElements.companyField).addClass('_required');
      $(formElements.vatIdField).show();
      if ($(formElements.vatIdField).hasClass('vat-required')) $(formElements.vatIdField).addClass('_required');

      if ($(formElements.pfpjField).length) {
        $(formElements.pfpjField).show();
        $(formElements.pfpjField).addClass('_required');
      }

      $(formElements.form).find('[name="billingAddressshared.firstname"] > .label').text($t('Contact person firstname'));
      $(formElements.form).find('[name="billingAddressshared.lastname"] > .label').text($t('Contact person lastname'));

      this.fieldErrorHandling($(formElements.companyField));
      if ($(formElements.vatIdField).hasClass('vat-required') && !$(formElements.vatIdField).hasClass('oandervalidate-length')) this.fieldErrorHandling($(formElements.vatIdField));
      this.fieldErrorHandling($(formElements.pfpjField));
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
      if (field.length && !field.hasClass('oandervalidate-length')) {
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
      }
      else {
        return true;
      }
    },

    /**
     * Set billing address
     * @returns {Void}
     */
    setBillingAddress: function (address) {
      var formElements = this.formElements();
      var currentLS = store.getLocalStorage();

      if (currentLS.billingAddress) {
        if (store.billingAddress.hasSelectedAddress() || currentLS.billingAddress.hasSelectedAddress) {
          var formInterval = setInterval(function () {
            if ($('[name="billingAddressshared.telephone"] .form-control').length) {
              for (var item in address) {
                var elem = formElements.form.querySelector('[name="' + item + '"]');
                var value = address[item];

                if (item == 'street') {
                  if (Array.isArray(value)) {
                    if (value.length > 1) {
                      for (var streetItem in value) {
                        elem = formElements.form.querySelector('[name="' + item + '['+ streetItem +']"]');
                        elem.value = value[streetItem];
                        elem.dispatchEvent(new Event('change'));
                      }
                    }
                    else {
                      elem = formElements.form.querySelector('[name="' + item + '[0]"]');
                      elem.value = value;
                      elem.dispatchEvent(new Event('change'));
                    }
                  }
                }

                if (item == 'vatId') {
                  elem = formElements.form.querySelector('[name="vat_id"]');
                }

                if (value !== undefined && value !== null && !Array.isArray(value)) {
                  if (elem) {
                    elem.value = value;
                    elem.dispatchEvent(new Event('change'));
                  }
                }
              }

              clearInterval(formInterval);
            }
          }, helpers.interval);
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

    sortCardAddress: function (address) {
      return billingAddressSort.sortCardAddress(address);
    },

    /**
     * New address
     * @returns {Void}
     */
    addNewAddress: function () {
      store.billingAddress.hasSelectedAddress(false);
      store.billingAddress.formIsVisible(true);
      billingAddressBase.scrollToForm($('#billing-new-address-form'));
    },

    selectBillingAddressItem: function (address) {
      var selectAddress = address;

      if (address == 'new') {
        selectAddress = store.billingAddress.newAddress();
      }

      if (customer.isLoggedIn() && addressList().length) {
        store.billingAddress.userSelectBillingAddress(true);
      }

      selectBillingAddress(selectAddress);
      store.billingAddress.hasSelectedAddress(true);
      store.billingAddress.formIsVisible(false);
    },

    /**
     * Billing continue
     * @returns {Void}
     */
    billingContinue: function () {
      var formElements = this.formElements();
      var activeTab = $(formElements.tabs).find('.tab__title.active').find('.tab__switch').attr('data-tab');

      if (store.billingAddress.formIsVisible() || addressList().length == 0) {
        store.billingAddress.userSelectBillingAddress(true);
      };

      if (addressList().length == 1
        && (typeof addressList()[0].isDefaultBilling() == "undefined" || addressList()[0].isDefaultBilling() == true)) {
        store.billingAddress.userSelectBillingAddress(true);
      }

      var vatIdFieldCondition = true;

      if ($(formElements.vatIdField).hasClass('vat-required')) {
        vatIdFieldCondition = this.watchField($(formElements.vatIdField));
      }
      else {
        vatIdFieldCondition = true;
      }

      if (store.billingAddress.continueBtn()) {
        if (activeTab == 'billing-company') {
          if (this.watchField($(formElements.companyField)) && this.watchField($(formElements.pfpjField))
            && vatIdFieldCondition) {
            this.updateAddress();
            store.billingAddress.formIsVisible(false);
          }
        }
        else {
          $(formElements.companyField).find('.form-control').val('').trigger('change');
          $(formElements.vatIdField).find('.form-control').val('').trigger('change');

          if ($(formElements.pfpjField).length) {
            $(formElements.pfpjField).find('.form-control').val('').trigger('change');
          }

          this.updateAddress();
          store.billingAddress.formIsVisible(false);

          $(formElements.tabs).find('.tab__title.active .tab__switch[data-tab="billing-person"]').trigger('click');
        }

        store.steps.billingAddress(true);
        store.steps.active('paymentMethod');
        $('.block--payment-method').find('.card__action').trigger('click');
      }
      else {
        if (activeTab == 'billing-company') {
          this.watchField($(formElements.companyField));
          if ($(formElements.vatIdField).hasClass('vat-required')) this.watchField($(formElements.vatIdField));

          if ($(formElements.pfpjField).length) {
            this.watchField($(formElements.pfpjField));
          }
        }
      }
    },

    selectedBillingContinue: function () {
      checkoutData.setSelectedBillingAddress(quote.billingAddress().getKey());
      store.steps.billingAddress(true);
      store.steps.active('paymentMethod');
      $('.block--payment-method').find('.card__action').trigger('click');
    },

    /**
		 * Check if card edit should be visible
		 * @returns {Boolean}
		 */
		isCardEditVisible: function(param) {
			return ko.computed(function() {
        var currentLS = store.getLocalStorage(),
            activeStep,
            visibleSteps,
            visible = ko.observable(true);

        if (store.steps.active() !== '') {
          activeStep = store.steps.active()
        } else {
          if (currentLS && currentLS.hasOwnProperty('steps') && currentLS.steps.hasOwnProperty('active')) {
            activeStep = currentLS.steps.active;
          } else {
            activeStep = 'auth';
          }
        }

        if (currentLS && currentLS.hasOwnProperty('steps') && currentLS.steps.hasOwnProperty('visible')) {
          visibleSteps = currentLS.steps.visible;
        } else {
          visibleSteps = ['auth'];
        }

				if (visibleSteps.indexOf(param) > -1) {
					if (store.steps.order.indexOf(activeStep) < store.steps.order.indexOf(param)) {
						visible(false);
					}
				}

				return visible();
			});
		},

    expressMessageWarning: ko.computed(function () {
      var currentLS = store.getLocalStorage();

			if (window.checkoutConfig.expressShippingConfig
				&& window.checkoutConfig.expressShippingConfig.fallback_msg) {
				if (store.shippingMethod) {
					if (store.shippingMethod.expressShippingIsValid()) {
						return window.checkoutConfig.expressShippingConfig.fallback_msg;
					}
          else {
            return '';
          }
				}

        if (currentLS.hasOwnProperty('shippingMethod')) {
          if (currentLS.shippingMethod.hasOwnProperty('expressShippingIsValid')) {
            if (currentLS.shippingMethod.expressShippingIsValid) {
              return window.checkoutConfig.expressShippingConfig.fallback_msg;
            }
            else {
              return '';
            }
          }
        }
			}
		}),

		expressMessageHandler: ko.computed(function () {
      var currentLS = store.getLocalStorage();

      if (quote.shippingAddress()
				&& quote.shippingAddress().postcode
				&& quote.shippingAddress().postcode !== null) {
				if (store.shippingMethod) {
					if (store.shippingMethod.expressShippingIsValid()) {
						return helpers.checkPostcodeExpressShipping(quote.shippingAddress().postcode);
					}
					else {
						return false;
					}
				}

				if (currentLS.hasOwnProperty('shippingMethod')) {
					if (currentLS.shippingMethod.hasOwnProperty('expressShippingIsValid')) {
						if (currentLS.shippingMethod.expressShippingIsValid) {
							return helpers.checkPostcodeExpressShipping(quote.shippingAddress().postcode);
						}
						else {
							return false;
						}
					}
				}
      }
		})
  };

  return function (target) {
    return target.extend(mixin);
  };
});
