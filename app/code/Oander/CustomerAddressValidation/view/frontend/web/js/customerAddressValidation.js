define([
  'mage/translate'
], function ($t) {
  'use strict';

  return function (config) {
    var customerAddressValidation = {
      defaults: {
        checkout: 'checkout-index-index',
        postcode: '[name="postcode"]',
        city: '[name="city"]',
        storecode: config.storeCode.split('_')[0].toUpperCase(),
        endpoint: window.location.protocol + '//'
          + window.location.hostname
          + '/rest/V1/checkout/address/getCityByZip',
        error: '',
      },

      /**
       * Create
       */
      _create: function () {
        if (document.body.classList.contains(this.defaults.checkout)) {
          console.log(this.defaults.storecode);
          this._validation();
        }
      },

      /**
       * Validation
       */
      _validation: function () {
        var self = this;

        document.addEventListener('focusout', function (event) {
          if (event.target.getAttribute('name') == 'postcode') {
            var value = event.target.value;
            var parentNode = self._closest(event.target, 'fieldset');

            if (!value) {
              self.defaults.error = 'postcode_number';
            }
            else {
              self._request(event.target.value, parentNode);
              self.defaults.error = '';
            }

            self._handlingError(parentNode);
          }
        });
      },

      /**
       * Request
       * @param {Number} postcode
       * @param {Element} parentNode
       */
      _request: function (postcode, parentNode) {
        var self = this;
        var postcode = postcode;
        var storecode = self.defaults.storecode;
        var request = new XMLHttpRequest();

        request.onreadystatechange = function () {
          if (this.readyState == 4 && this.status == 200) {
            self._fillCity(JSON.parse(this.responseText), parentNode);
          }
        }

        request.open('GET',
          self.defaults.endpoint + '?countrycode=' + storecode + '&zipcode=' + postcode,
          true);
        request.send();
      },

      /**
       * Fill city
       * @param {String} city
       * @param {Element} parentNode
       */
      _fillCity: function (city, parentNode) {
        var self = this;
        var cityField = parentNode.querySelector(self.defaults.city);

        if (city !== false) {
          cityField.value = city;
          cityField.dispatchEvent(new Event('change'));
        } else {
          self.defaults.error = 'Unknown ZIP code, please recheck it!';
        }

        self._handlingError(parentNode);
      },

      /**
       * Handling error
       * @param {Element} parentNode
       */
      _handlingError: function (parentNode) {
        var self = this;
        var error = self.defaults.error;
        var element = parentNode.querySelector(self.defaults.postcode);

        if (error && error != 'postcode_number') {
          self._removeWarning(parentNode.getElementsByClassName('mage-warning')[0], element);
          self._addWarning(element);
        }
        else {
          self._removeWarning(parentNode.getElementsByClassName('mage-warning')[0], element);
        }
      },

      /**
       * Add warning
       * @param {Element} element
       */
      _addWarning: function (element) {
        var error = this.defaults.error;
        var errorElement = document.createElement('div');

        errorElement.innerHTML = $t(error);
        errorElement.classList.add('mage-warning');
        element.parentNode.insertBefore(errorElement, element.nextSibling);
        element.classList.add('_warning');
      },

      /**
       * Remove warning
       * @param {Element} warningElement
       * @param {Element} element
       */
      _removeWarning: function (warningElement, element) {
        if (warningElement) {
          element.parentNode.removeChild(warningElement);
          element.classList.remove('_warning');
        }
      },

      /**
       * Find parent node by class (closest)
       * @param {Element} el
       * @param {String} cls
       * @returns {Element}
       */
      _closest: function (el, cls) {
        while ((el = el.parentNode) && !el.classList.contains(cls));
        return el;
      }
    };

    customerAddressValidation._create();
  }
});
