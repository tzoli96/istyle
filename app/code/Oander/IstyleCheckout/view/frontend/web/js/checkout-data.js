/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global alert*/
/**
 * Checkout adapter for customer data storage
 */
 define([
  'jquery',
  'Magento_Customer/js/customer-data'
], function ($, storage) {
  'use strict';

  var cacheKey = 'checkout-data';

  var getCustomerInfo = function () {
    var customer = storage.get('customer');
    return customer();
  };

  var getCustomerId = function (customerInfo) {
    customerInfo = customerInfo || getCustomerInfo();
    return customerInfo && customerInfo.data_id;
  };

  var getData = function () {
    return storage.get(cacheKey)();
  };

  var saveData = function (checkoutData) {
    storage.set(cacheKey, checkoutData);
  };

  var storageData = function () {
    var data = JSON.parse(localStorage.getItem('mage-cache-storage'))['checkout-data'];

    if (getCustomerId() != null) {
      return getData();
    }
    else {
      if (data) return data;
      return {
        'selectedShippingAddress': null,
        'shippingAddressFromData': null,
        'newCustomerShippingAddress': null,
        'selectedShippingRate': null,
        'selectedPaymentMethod': null,
        'selectedBillingAddress': null,
        'billingAddressFormData': null,
        'newCustomerBillingAddress': null
      };
    }
  }

  var checkData = function () {
    var value = true;

    for (var key in storageData()) {
      if (storageData()[key] !== 'null') value = false;
      break;
    }

    return value;
  };

  if (checkData()) {
    var checkoutData = {
      'selectedShippingAddress': null,
      'shippingAddressFromData': null,
      'newCustomerShippingAddress': null,
      'selectedShippingRate': null,
      'selectedPaymentMethod': null,
      'selectedBillingAddress': null,
      'billingAddressFormData': null,
      'newCustomerBillingAddress': null
    };
    saveData(checkoutData);
  }

  return {
    setSelectedShippingAddress: function (data) {
      var obj = storageData();
      obj.selectedShippingAddress = data;
      saveData(obj);
    },

    getSelectedShippingAddress: function () {
      return storageData().selectedShippingAddress;
    },

    setShippingAddressFromData: function (data) {
      var obj = storageData();
      obj.shippingAddressFromData = data;
      saveData(obj);
    },

    getShippingAddressFromData: function () {
      return storageData().shippingAddressFromData;
    },

    setNewCustomerShippingAddress: function (data) {
      var obj = storageData();
      obj.newCustomerShippingAddress = data;
      saveData(obj);
    },

    getNewCustomerShippingAddress: function () {
      return storageData().newCustomerShippingAddress;
    },

    setSelectedShippingRate: function (data) {
      var obj = storageData();
      obj.selectedShippingRate = data;
      saveData(obj);
    },

    getSelectedShippingRate: function () {
      return storageData().selectedShippingRate;
    },

    setSelectedPaymentMethod: function (data) {
      var obj = storageData();
      obj.selectedPaymentMethod = data;
      saveData(obj);
    },

    getSelectedPaymentMethod: function () {
      return storageData().selectedPaymentMethod;
    },

    setSelectedBillingAddress: function (data) {
      var obj = storageData();
      obj.selectedBillingAddress = data;
      saveData(obj);
    },

    getSelectedBillingAddress: function () {
      return storageData().selectedBillingAddress;
    },

    setBillingAddressFromData: function (data) {
      var obj = storageData();
      obj.billingAddressFromData = data;
      saveData(obj);
    },

    getBillingAddressFromData: function () {
      return storageData().billingAddressFromData;
    },

    setNewCustomerBillingAddress: function (data) {
      var obj = storageData();
      obj.newCustomerBillingAddress = data;
      saveData(obj);
    },

    getNewCustomerBillingAddress: function () {
      return storageData().newCustomerBillingAddress;
    },

    getValidatedEmailValue: function () {
      var obj = storageData();
      return (obj.validatedEmailValue) ? obj.validatedEmailValue : '';
    },

    setValidatedEmailValue: function (email) {
      var obj = storageData();
      obj.validatedEmailValue = email;
      saveData(obj);
    },

    getInputFieldEmailValue: function () {
      var obj = storageData();
      return (obj.inputFieldEmailValue) ? obj.inputFieldEmailValue : '';
    },

    setInputFieldEmailValue: function (email) {
      var obj = storageData();
      obj.inputFieldEmailValue = email;
      saveData(obj);
    }
  }
});
