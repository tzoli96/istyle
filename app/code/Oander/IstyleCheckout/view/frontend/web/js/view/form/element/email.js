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

  if (validatedEmail && !customer.isLoggedIn()) {
    quote.guestEmail = validatedEmail;
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
    checkDelay: 2000,
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

      store.setLocalStorage();

      this.isPasswordVisible.subscribe(function (value) {
        if (value) this.passwordHasChanged();
      }, this);

      if (store.getLocalStorage()) this.localStorageHandler(store.getLocalStorage());

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
          self.checkEmailAvailability();
        } else {
          self.isPasswordVisible(false);
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
        store.auth.emailHasUser(false);
      }).fail(function () {
        self.isFirstnameExist(self.checkRequest);
        self.isPasswordVisible(true);
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

      loginForm.validation();

      validator = loginForm.validate();

      if (!this.email()) {
        !$(emailSelector).valid();
      }
      else {
        $(emailSelector).valid();
      }

      if (validator.check(emailSelector)) {
        store.auth.hasValidEmailAddress(true);
        store.auth.emailHasUser(false);
        store.auth.errorMessage(false);
      }
      else {
        store.auth.hasValidEmailAddress(false);
        store.auth.emailHasUser(false);
        store.auth.hasPasswordValue(false);
        store.auth.errorMessage(false);
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
        : checkoutData.getInputFieldEmailValue();
    },

    /**
     * Is firstname exist
     * @param {String} values
     * @returns {Void}
     */
     isFirstnameExist: function (values) {
      var values = JSON.parse(values.responseJSON);

      if (values.firstname) {
        store.auth.emailHasUser(true);
        this.firstname(values.firstname);
      }
      else {
        store.auth.emailHasUser(false);
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
        if (localStorage.auth.hasValidEmailAddress) {
          var loginFormSelector = $('form[data-role=email-with-possible-login]');
          var email = loginFormSelector.find('[name="username"]');

          email.focus();

          store.auth.hasValidEmailAddress(true);
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
        loginAction(loginData).always(function() {
          fullScreenLoader.stopLoader();
        });
      }
    },

    forgotPasswordOpenModal: function () {
      forgotPassword.openModal();
    }
  });
});
