define([
], function () {
  'use strict';

  var sort = {
    /**
     * Get address attributes positions
     * @returns {[key: string]: any}
     */
    getAddressAttributesPositions: function () {
      return window.checkoutConfig.addressAttributesPositions;
    },

    /**
     * Sort fields
     * @param {string} formId
     * @returns {void}
     */
    sortFields: function (formId) {
      var self = this;
      var positions = this.getAddressAttributesPositions();

      if (positions) {
        for (var field in positions) {
          var orders = positions[field];
          var parent = document.querySelector('.form--billing-address');
          var elem = parent.querySelector('.form-group[name="billingAddressshared.' + field + '"]');

          if (field == 'street') {
            elem = parent.querySelector('.form-group.street');
            self.streetFieldHandler(elem);
          }

          if (field == 'pfpj_reg_no') {
            elem = parent.querySelector('.form-group[name="billingAddressshared.custom_attributes.pfpj_reg_no"]');
          }

          if (field == 'region') {
            elem = parent.querySelector('.form-group[name="billingAddress.region"]');
          }

          switch (formId) {
            case 'billing-person':
              self.setOrder(elem, orders.individual_position, orders.width);
              break;
            case 'billing-company':
              self.setOrder(elem, orders.company_position, orders.width);
              break;
          }
        }
      }
    },

    /**
     * Add order
     * @param {HTMLDivElement} elem
     * @param {number} order
     * @returns {void}
     */
    setOrder: function (elem, order, width) {
      var billingAddressBlock = document.querySelector('.block.block--billing-address');

      if (elem) {
        var field = elem.querySelector('.form-control');

        if (elem.classList.contains('street')) {
          field = elem.querySelector('[name="billingAddressshared.street.0"] .form-control');
        }

        if (order !== null) {
          elem.style.order = order;
          if (field) field.setAttribute('tabindex', order);
        }

        if (width === 100) elem.classList.add('w-100');

        billingAddressBlock.classList.remove('is-loading');
      }
    },

    /**
     * Street field handler
     * @param {HTMLDivElement} elem
     * @returns {void}
     */
    streetFieldHandler: function (elem) {
      var fields = elem.querySelectorAll('.field.form-group');
      if (fields.length > 1) elem.classList.add('has-multiple-fields');
    },

    /**
     * Sort card address
     * @param {[key: string]: any} address
     * @param {string} fixPosition
     * @returns {void}
     */
    sortCardAddress: function (address, fixPosition) {
      var positions = this.getAddressAttributesPositions();
      var value = {};
      var hasCompany = false;

      positions['vatId'] = positions['vat_id'];

      if (address.customAttributes) {
        if (address.customAttributes.pfpj_reg_no) {
          address['pfpj_reg_no'] = address.customAttributes.pfpj_reg_no;
        }
      }

      for (var data in address) {
        for (var p in positions) {
          if (p == data && address[data]) {
            var position = (address.company)
                            ? positions[p].company_position
                            : positions[p].individual_position;

            if (fixPosition == 'individual') position = positions[p].individual_position;
            value[position] = address[data];

            (address.company)
              ? hasCompany = true
              : '';

            if (fixPosition == 'individual') hasCompany = false;
          }
        }
      }

      value = this.sortObject(value);

      return this.renderCards(value, hasCompany);
    },

    /**
     * Sort object
     * @param {[key: string]: any} obj
     * @returns {[key: string]: any}
     */
    sortObject: function (obj) {
      return Object.keys(obj).sort().reduce(function (result, key) {
        result[key] = obj[key];
        return result;
      }, {});
    },

    /**
     * Render cards
     * @param {[key: string]: any} value
     * @param {boolean} hasCompany
     * @returns {HTMLDivElement}
     */
    renderCards: function (value, hasCompany) {
      var card = '';
      var cardHead = '';
      var cardContent = '';
      var i = 0;

      for (var v in value) {
        var elem = value[v];

        if (i < 2) {
          if (hasCompany) cardHead += '<span>' + elem + '</span>';
          else cardHead += elem + ' ';
        }
        else {
          cardContent += '<span>' + elem + '</span>';
        }

        i++;
      }

      card = '<div class="card__head">' + cardHead + '</div><div class="card__content">' + cardContent + '</div>';
      return card;
    },
  };

  return sort;
});
