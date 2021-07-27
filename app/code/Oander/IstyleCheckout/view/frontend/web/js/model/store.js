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
    localStorageObject: {
      auth: {
        hasValidEmailAddress: false,
        emailHasUser: false,
        hasPasswordValue: false,
      },
    },

    /**
     * Set local storage
     * @returns {Void}
     */
    setLocalStorage: function () {
      if (!localStorage.getItem('istyle-checkout')) localStorage.setItem('istyle-checkout', JSON.stringify({}));

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
