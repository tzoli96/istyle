define([
  'ko'
], function (ko) {
  'use strict';

  var store = {
    steps: {
      auth: ko.observable(false),
      shippingMethod: ko.observable(false),
      shippingAddress: ko.observable(false),
      billingAddress: ko.observable(false),
      paymentMethod: ko.observable(false),
    },
    auth: {
      hasValidEmailAddress: ko.observable(false),
      emailHasUser: ko.observable(false),
      hasPasswordValue: ko.observable(false),
      errorMessage: ko.observable(false),
    },
    shippingAddress: {
      selectedShippingAddress: ko.observable(false),
      continueBtn: ko.observable(false),
    },
    billingAddress: {
      selectedBillingAddress: ko.observable({}),
      hasSelectedAddress: ko.observable(false),
      newAddress: ko.observable({}),
      hasNewAddress: ko.observable(false),
      tabSelector: ko.observable('billing-person'),
      formIsVisible: ko.observable(false),
      continueBtn: ko.observable(false),
    },
    localStorageObject: {
      auth: {
        hasValidEmailAddress: false,
        emailHasUser: false,
        hasPasswordValue: false,
      },
      shippingAddress: {
        selectedShippingAddress: false,
      },
      billingAddress: {
        selectedBillingAddress: {},
        hasSelectedAddress: false,
        newAddress: {},
        hasNewAddress: false,
        tabSelector: 'billing-person',
        formIsVisible: false,
        continueBtn: ko.observable(false),
      }
    },

    /**
     * Set local storage
     * @returns {Void}
     */
    setLocalStorage: function () {
      if (!localStorage.getItem('istyle-checkout')) localStorage.setItem('istyle-checkout', JSON.stringify({}));

      // Auth
      this.auth.hasValidEmailAddress.subscribe(function (value) {
        this.localStorageObject.auth.hasValidEmailAddress = value;
        this.updateLocalStorage();
      }, this);

      this.auth.emailHasUser.subscribe(function (value) {
        this.localStorageObject.auth.emailHasUser = value;
        this.updateLocalStorage();
      }, this);

      this.auth.hasPasswordValue.subscribe(function (value) {
        this.localStorageObject.auth.hasPasswordValue = value;
        this.updateLocalStorage();
      }, this);

      // Billing address
      this.billingAddress.selectedBillingAddress.subscribe(function (value) {
        this.localStorageObject.billingAddress.selectedBillingAddress = value;
        this.updateLocalStorage();
      }, this);

      this.billingAddress.hasSelectedAddress.subscribe(function (value) {
        this.localStorageObject.billingAddress.hasSelectedAddress = value;
        this.updateLocalStorage();
      }, this);

      this.billingAddress.newAddress.subscribe(function (value) {
        this.localStorageObject.billingAddress.newAddress = value;
        this.updateLocalStorage();
      }, this);

      this.billingAddress.hasNewAddress.subscribe(function (value) {
        this.localStorageObject.billingAddress.hasNewAddress = value;
        this.updateLocalStorage();
      }, this);

      this.billingAddress.tabSelector.subscribe(function (value) {
        this.localStorageObject.billingAddress.tabSelector = value;
        this.updateLocalStorage();
      }, this);

      this.billingAddress.formIsVisible.subscribe(function (value) {
        this.localStorageObject.billingAddress.formIsVisible = value;
        this.updateLocalStorage();
      }, this);

      this.billingAddress.continueBtn.subscribe(function (value) {
        this.localStorageObject.billingAddress.continueBtn = value;
        this.updateLocalStorage();
      }, this);
    },

    /**
     * Update localStorage
     * @returns {Void}
     */
    updateLocalStorage: function () {
      localStorage.setItem('istyle-checkout', JSON.stringify(this.localStorageObject));
    },

    /**
     * Get localStorage
     * @returns {Object | Boolean}
     */
    getLocalStorage: function () {
      if (localStorage.getItem('istyle-checkout')) return JSON.parse(localStorage.getItem('istyle-checkout'));
      return false;
    },
  };

  return store;
});
