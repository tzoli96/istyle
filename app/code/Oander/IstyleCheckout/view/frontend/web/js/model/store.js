define([
  'ko',
  'Magento_Customer/js/model/customer',
], function (ko, customer) {
  'use strict';

  var store = {
    steps: {
      auth: ko.observable(false),
      shippingMethod: ko.observable(false),
      shippingAddress: ko.observable(false),
      billingAddress: ko.observable(false),
      paymentMethod: ko.observable(false),
      active: ko.observable(''),
    },
    auth: {
      hasValidEmailAddress: ko.observable(false),
      emailValue: ko.observable(''),
      emailHasUser: ko.observable(false),
      hasPasswordValue: ko.observable(false),
      errorMessage: ko.observable(false),
    },
    shippingMethod: {
      selectedTitle: ko.observable(''),
      selectedCode: ko.observable(''),
      continueBtn: ko.observable(false),
      unityPickupSettlement: ko.observable(''),
      unityPickupType: ko.observable(false),
      unityPickupPoint: ko.observable(''),
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
      formIsVisible: ko.observable(false),
      continueBtn: ko.observable(false),
      userSelectBillingAddress: ko.observable(false),
    },
    localStorageObject: {
      steps: {
        auth: false,
        shippingMethod: false,
        shippingAddress: false,
        billingAddress: false,
        paymentMethod: false,
        active: 'auth',
      },
      auth: {
        hasValidEmailAddress: false,
        emailValue: '',
        emailHasUser: false,
        hasPasswordValue: false,
      },
      shippingMethod: {
        selectedTitle: '',
        selectedCode: '',
        continueBtn: false,
        unityPickupSettlement: '',
        unityPickupType: false,
        unityPickupPoint: '',
      },
      shippingAddress: {
        selectedShippingAddress: false,
      },
      billingAddress: {
        selectedBillingAddress: {},
        hasSelectedAddress: false,
        newAddress: {},
        hasNewAddress: false,
        formIsVisible: false,
        continueBtn: false,
        userSelectBillingAddress: false,
      },
    },

    /**
     * Set local storage
     * @returns {Void}
     */
    setLocalStorage: function () {
      if (!localStorage.getItem('istyle-checkout')) localStorage.setItem('istyle-checkout', JSON.stringify({}));

      this.checkSteps();

      // Steps
      this.steps.auth.subscribe(function (value) {
        this.localStorageObject.steps.auth = value;
        this.updateLocalStorage('steps', 'auth');
      }, this);

      this.steps.shippingMethod.subscribe(function (value) {
        this.localStorageObject.steps.shippingMethod = value;
        this.updateLocalStorage('steps', 'shippingMethod');
      }, this);

      this.steps.shippingAddress.subscribe(function (value) {
        this.localStorageObject.steps.shippingAddress = value;
        this.updateLocalStorage('steps', 'shippingAddress');
      }, this);

      this.steps.billingAddress.subscribe(function (value) {
        this.localStorageObject.steps.billingAddress = value;
        this.updateLocalStorage('steps', 'billingAddress');
      }, this);

      this.steps.paymentMethod.subscribe(function (value) {
        this.localStorageObject.steps.paymentMethod = value;
        this.updateLocalStorage('steps', 'paymentMethod');
      }, this);

      this.steps.active.subscribe(function (value) {
        this.localStorageObject.steps.active = value;
        this.updateLocalStorage('steps', 'active');
      }, this);

      // Auth
      this.auth.hasValidEmailAddress.subscribe(function (value) {
        this.localStorageObject.auth.hasValidEmailAddress = value;
        this.updateLocalStorage('auth', 'hasValidEmailAddress');
      }, this);

      this.auth.emailValue.subscribe(function (value) {
        this.localStorageObject.auth.emailValue = value;
        this.updateLocalStorage('auth', 'emailValue');
      }, this);

      this.auth.emailHasUser.subscribe(function (value) {
        this.localStorageObject.auth.emailHasUser = value;
        this.updateLocalStorage('auth', 'emailHasUser');
      }, this);

      this.auth.hasPasswordValue.subscribe(function (value) {
        this.localStorageObject.auth.hasPasswordValue = value;
        this.updateLocalStorage('auth', 'hasPasswordValue');
      }, this);

      // Shipping method
      this.shippingMethod.selectedTitle.subscribe(function (value) {
        this.localStorageObject.shippingMethod.selectedTitle = value;
        this.updateLocalStorage('shippingMethod', 'selectedTitle');
      }, this);

      this.shippingMethod.selectedCode.subscribe(function (value) {
        this.localStorageObject.shippingMethod.selectedCode = value;
        this.updateLocalStorage('shippingMethod', 'selectedCode');
      }, this);

      this.shippingMethod.continueBtn.subscribe(function (value) {
        this.localStorageObject.shippingMethod.continueBtn = value;
        this.updateLocalStorage('shippingMethod', 'continueBtn');
      }, this);

      this.shippingMethod.unityPickupSettlement.subscribe(function (value) {
        this.localStorageObject.shippingMethod.unityPickupSettlement = value;
        this.updateLocalStorage('shippingMethod', 'unityPickupSettlement');
      }, this);

      this.shippingMethod.unityPickupType.subscribe(function (value) {
        this.localStorageObject.shippingMethod.unityPickupType = value;
        this.updateLocalStorage('shippingMethod', 'unityPickupType');
      }, this);

      this.shippingMethod.unityPickupPoint.subscribe(function (value) {
        this.localStorageObject.shippingMethod.unityPickupPoint = value;
        this.updateLocalStorage('shippingMethod', 'unityPickupPoint');
      }, this);

      // Billing address
      this.billingAddress.selectedBillingAddress.subscribe(function (value) {
        this.localStorageObject.billingAddress.selectedBillingAddress = value;
        this.updateLocalStorage('billingAddress', 'selectedBillingAddress');
      }, this);

      this.billingAddress.hasSelectedAddress.subscribe(function (value) {
        this.localStorageObject.billingAddress.hasSelectedAddress = value;
        this.updateLocalStorage('billingAddress', 'hasSelectedAddress');
      }, this);

      this.billingAddress.newAddress.subscribe(function (value) {
        this.localStorageObject.billingAddress.newAddress = value;
        this.updateLocalStorage('billingAddress', 'newAddress');
      }, this);

      this.billingAddress.hasNewAddress.subscribe(function (value) {
        this.localStorageObject.billingAddress.hasNewAddress = value;
        this.updateLocalStorage('billingAddress', 'hasNewAddress');
      }, this);

      this.billingAddress.formIsVisible.subscribe(function (value) {
        this.localStorageObject.billingAddress.formIsVisible = value;
        this.updateLocalStorage('billingAddress', 'formIsVisible');
      }, this);

      this.billingAddress.continueBtn.subscribe(function (value) {
        this.localStorageObject.billingAddress.continueBtn = value;
        this.updateLocalStorage('billingAddress', 'continueBtn');
      }, this);

      this.billingAddress.userSelectBillingAddress.subscribe(function (value) {
        this.localStorageObject.billingAddress.userSelectBillingAddress = value;
        this.updateLocalStorage('billingAddress', 'userSelectBillingAddress');
      }, this);
    },

    /**
     * Update localStorage
     * @returns {Void}
     */
    updateLocalStorage: function (object, value) {
      var currentLS = this.getLocalStorage();

      if (!currentLS[object]) {
        if (!value) {
          currentLS[object] = this.localStorageObject[object];
        }
        else {
          currentLS[object] = {};
          currentLS[object][value] = this.localStorageObject[object][value];
        }
      }
      else {
        currentLS[object][value] = this.localStorageObject[object][value];
      }

      localStorage.setItem('istyle-checkout', JSON.stringify(currentLS));
    },

    /**
     * Check steps
     * @returns {Void}
     */
    checkSteps: function () {
      var currentLS = this.getLocalStorage();

      if (!currentLS.steps || currentLS.steps.auth === false) {
        if (customer.isLoggedIn()) {
          this.steps.auth(true);
          this.steps.active('shippingMethod');

          this.localStorageObject.steps.auth = true;
          this.localStorageObject.steps.active = 'shippingMethod';
        }

        if (window.checkoutConfig.checkoutUrl.indexOf('reservation_id') > -1) {
          this.steps.auth(true);
          this.steps.shippingMethod(true);
          this.steps.shippingAddress(true);
          this.steps.active('billingAddress');

          this.localStorageObject.steps.auth = true;
          this.localStorageObject.steps.shippingMethod = true;
          this.localStorageObject.steps.shippingAddress = true;
          this.localStorageObject.steps.active = 'billingAddress';
        }

        currentLS['steps'] = this.localStorageObject.steps;

        localStorage.setItem('istyle-checkout', JSON.stringify(currentLS));
      }
      else {
        if (!currentLS['steps']) {
          for (var step in currentLS['steps']) {
            this.steps[step] = ko.observable(currentLS['steps'][step]);
          }
        }
        else {
          if (customer.isLoggedIn() && currentLS.steps.active == 'auth') {
            this.steps.auth(true);
            this.steps.active('shippingMethod');
  
            this.localStorageObject.steps.auth = true;
            this.localStorageObject.steps.active = 'shippingMethod';

            currentLS['steps'] = this.localStorageObject.steps;

            localStorage.setItem('istyle-checkout', JSON.stringify(currentLS));
          }
        }
      }
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
