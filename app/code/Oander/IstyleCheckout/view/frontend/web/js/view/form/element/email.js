/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
  'jquery',
  'uiComponent',
  'ko',
  'Magento_Customer/js/model/customer',
  'Magento_Customer/js/action/check-email-availability',
  'Magento_Customer/js/action/login',
  'Magento_Checkout/js/model/quote',
  'Magento_Checkout/js/checkout-data',
  'Magento_Checkout/js/model/full-screen-loader',
  'mage/translate',
  'Oander_IstyleCheckout/js/model/store',
  'Oander_IstyleCheckout/js/view/form/element/forgotpassword',
  'mage/validation',
], function ($, Component, ko, customer, checkEmailAvailability, loginAction, quote, checkoutData, fullScreenLoader, $t, store, forgotPassword) {
  'use strict';

  var validatedEmail = checkoutData.getValidatedEmailValue();
  var currentLS = store.getLocalStorage();

  if (validatedEmail && !customer.isLoggedIn()) {
    quote.guestEmail = validatedEmail;
  }

  if (currentLS.auth) {
    if (currentLS.auth.emailValue) {
      validatedEmail = currentLS.auth.emailValue;
      quote.guestEmail = currentLS.auth.emailValue;
    }
  }

  return Component.extend({
    defaults: {
      template: 'Magento_Checkout/form/element/email',
      email: (customer.isLoggedIn()) ? window.checkoutConfig.customerData.email : checkoutData.getInputFieldEmailValue(),
      emailFocused: false,
      isLoading: false,
      isPasswordVisible: false,
      firstname: false,
      listens: {
        email: 'emailHasChanged',
        emailFocused: 'validateEmail',
      }
    },
    checkDelay: 1000,
    checkRequest: null,
    isEmailCheckComplete: null,
    isCustomerLoggedIn: customer.isLoggedIn,
    forgotPasswordUrl: window.checkoutConfig.forgotPasswordUrl,
    emailCheckTimeout: 0,
    emailTitle: $t('E-mail address'),
    passwordTitle: $t('Password'),
    auth: {
      hasValidEmailAddress: store.auth.hasValidEmailAddress,
      emailHasUser: store.auth.emailHasUser,
      hasPasswordValue: store.auth.hasPasswordValue,
      errorMessage: store.auth.errorMessage,
    },
    emailFieldStatus: forgotPassword.emailFieldStatus,
    emailMessage: forgotPassword.emailMessage,

    /**
     * Initializes observable properties of instance
     *
     * @returns {Object} Chainable.
     */
    initObservable: function () {
      this._super()
        .observe(['email', 'emailFocused', 'isLoading', 'isPasswordVisible', 'firstname']);

      var currentLS = store.getLocalStorage();

      store.setLocalStorage();

      this.isPasswordVisible.subscribe(function (value) {
        if (value) this.passwordHasChanged();
      }, this);

      if (store.getLocalStorage()) this.localStorageHandler(store.getLocalStorage());

      if (currentLS.auth) {
        if (currentLS.auth.emailValue) {
          if (!this.email()) this.email(currentLS.auth.emailValue);
        }
      }

      return this;
    },

    /**
     * Callback on changing email property
     */
    emailHasChanged: function () {
      var self = this;

      clearTimeout(this.emailCheckTimeout);

      if (self.validateEmail()) {
        quote.guestEmail = self.email();
        checkoutData.setValidatedEmailValue(self.email());
      }
      this.emailCheckTimeout = setTimeout(function () {
        if (self.validateEmail()) {
          if (store.auth.emailHasUser() == true) {
            if (store.auth.hasValidEmailAddress() == false) {
              self.checkEmailAvailability();
            }
          }
          else {
            if (store.auth.hasValidEmailAddress() == false) {
              self.checkEmailAvailability();
            }
          }
        } else {
          self.isPasswordVisible(false);
          store.auth.hasValidEmailAddress(false);
          store.auth.emailHasUser(false);
          $('form[data-role=email-with-possible-login]').find('[name="password"]').val('');
          store.auth.hasPasswordValue(false);
        }
      }, self.checkDelay);

      checkoutData.setInputFieldEmailValue(self.email());
    },

    /**
     * Check email existing.
     */
    checkEmailAvailability: function () {
      var self = this;
      this.validateRequest();
      this.isEmailCheckComplete = $.Deferred();
      this.isLoading(true);
      this.checkRequest = checkEmailAvailability(this.isEmailCheckComplete, this.email());

      $.when(this.isEmailCheckComplete).done(function () {
        self.isPasswordVisible(false);

        store.auth.hasValidEmailAddress(true);
        store.auth.emailHasUser(false);
        $('form[data-role=email-with-possible-login]').find('[name="password"]').val('');
        store.auth.hasPasswordValue(false);
        store.auth.errorMessage(false);
      }).fail(function () {
        self.isFirstnameExist(self.checkRequest);
        self.isPasswordVisible(true);

        if (self.validateEmail()) {
          store.auth.hasValidEmailAddress(true);
        }
        else {
          store.auth.hasValidEmailAddress(false);
        }

        store.auth.emailHasUser(true);
        store.auth.hasPasswordValue(false);
        store.auth.errorMessage(false);
      }).always(function () {
        self.isLoading(false);
      });
    },

    /**
     * If request has been sent -> abort it.
     * ReadyStates for request aborting:
     * 1 - The request has been set up
     * 2 - The request has been sent
     * 3 - The request is in process
     */
    validateRequest: function () {
      if (this.checkRequest != null && $.inArray(this.checkRequest.readyState, [1, 2, 3])) {
        this.checkRequest.abort();
        this.checkRequest = null;
      }
    },

    /**
     * Local email validation.
     *
     * @param {Boolean} focused - input focus.
     * @returns {Boolean} - validation result.
     */
    validateEmail: function (focused) {
      var loginFormSelector = 'form[data-role=email-with-possible-login]',
        emailSelector = loginFormSelector + ' input[name=username]',
        loginForm = $(loginFormSelector),
        validator;

      store.auth.emailValue(this.email());

      loginForm.validation();

      validator = loginForm.validate();

      $(emailSelector).on('keydown', function (e) {
        var code = (e.keyCode || e.which);

        if (code == 37 || code == 38 || code == 39 || code == 40 || code == 91) {
          return;
        }

        store.auth.hasValidEmailAddress(false);
      });

      if (focused == false) {
        if (this.email()) {
          if (store.auth.emailHasUser() == false) {
            if (store.auth.hasValidEmailAddress() == false) {
              this.emailHasChanged();
            }
          }
        }
      }

      if (focused === false && !!this.email()) {
        return !!$(emailSelector).valid();
      }

      return validator.check(emailSelector);
    },

    /**
     * Get email value
     * @returns {string}
     */
    getEmailValue: function () {
      return (this.email()) ? this.email() : false;
    },

    /**
     * Get email
     * @returns {string}
     */
    getEmail: function () {
      return (customer.isLoggedIn())
        ? window.checkoutConfig.customerData.email
        : this.email();
    },

    /**
     * Is firstname exist
     * @param {String} values
     * @returns {Void}
     */
    isFirstnameExist: function (values) {
      var valuesParse = values.responseJSON ? JSON.parse(values.responseJSON) : '';

      if (valuesParse) {
        if (valuesParse.firstname) {
          store.auth.emailHasUser(true);
          this.firstname(valuesParse.firstname);
        }
        else {
          store.auth.emailHasUser(false);
        }
      }
    },

    /**
     * Password has changed
     * @returns {Void}
     */
    passwordHasChanged: function () {
      var self = this;
      var loginFormSelector = $('form[data-role=email-with-possible-login]');
      var passwordInput = loginFormSelector.find('[name="password"]');

      if (passwordInput.length) {
        this.passwordStoreChanges(passwordInput.val());

        passwordInput.on('keyup', function () {
          self.passwordStoreChanges(passwordInput.val());
        });

        self.checkAutofill(true);
      }
    },

    /**
     * Password store changes
     * @param {String} value
     * @returns {Void}
     */
    passwordStoreChanges: function (value) {
      if (value.length > 0) {
        store.auth.hasPasswordValue(true);
      }
      else {
        store.auth.hasPasswordValue(false);
      }
    },

    /**
     * localStorage handler
     * @param {Object} localStorage
     * @returns {Void}
     */
    localStorageHandler: function (localStorage) {
      if (localStorage.auth) {
        var loginFormSelector = $('form[data-role=email-with-possible-login]');
        var email = loginFormSelector.find('[name="username"]');

        if (localStorage.auth.hasValidEmailAddress) {
          email.focus();
          store.auth.hasValidEmailAddress(true);
        }

        if (localStorage.auth.emailValue) {
          this.checkEmailAvailability();
        }

        if (localStorage.auth.emailHasUser) {
          store.auth.emailHasUser(true);
          this.checkEmailAvailability();
        }
      }
    },

    /**
     * Log in form submitting callback.
     *
     * @param {HTMLElement} loginForm - form element.
     */
    login: function (loginForm) {
      var loginData = {},
        formDataArray = $(loginForm).serializeArray();

      formDataArray.forEach(function (entry) {
        loginData[entry.name] = entry.value;
      });

      if (this.isPasswordVisible() && $(loginForm).validation() && $(loginForm).validation('isValid')) {
        fullScreenLoader.startLoader();
        store.auth.errorMessage(false);
        loginAction(loginData).always(function () {
          fullScreenLoader.stopLoader();
        });
      }
    },

    forgotPasswordOpenModal: function () {
      forgotPassword.openModal();
    },

    authContinue: function () {
      if (store.auth.hasValidEmailAddress()) {
        store.steps.auth(true);
        store.steps.active('shippingMethod');
        $('.block--shipping-method').find('.card__action').trigger('click');
      }
    },

    checkAutofill: function(param) {
      var self = this,
          inputField = $('#customer-email'),
          passwordField = $('#customer-password'),
          emailTrigger = function() {
            setTimeout(function() {
              if (inputField.is(':-webkit-autofill') || inputField.val() !== '') {
                $('.block--authentication').trigger('click');
                inputField.focus();
                inputField.trigger('blur change keyup keydown keypress input');
                self.emailHasChanged();
                passwordTrigger();
              }
            }, self.checkDelay);
          },
          passwordTrigger = function() {
            setTimeout(function() {
              if (passwordField.is(':visible') && passwordField.is(':-webkit-autofill') || passwordField.val() !== '') {
                store.auth.hasPasswordValue(true);
              }
            }, self.checkDelay);
          };

      param ? passwordTrigger() : emailTrigger();
    },
    showHidePassword: function() {
      var icon = document.querySelector('.password-eye-icon'),
          input = document.getElementById('customer-password');

      icon.addEventListener('click', function (event) {
        icon.classList.toggle('show');
        if (input.type === 'password') {
          input.type = 'text';
        } else {
          input.type = 'password';
        }
      });
    }
  });
});
