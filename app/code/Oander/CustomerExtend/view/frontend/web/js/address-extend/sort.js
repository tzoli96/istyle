define([
], function () {
    'use strict';

    return {
        /**
         * Get address attributes positions
         * @returns {[key: string]: any}
         */
        getAddressAttributesPositions: function () {
            // TODO comes from BE somewhere
            //return window.checkoutConfig.addressAttributesPositions;
            var addressPositions =
                {
                    "lastname": {
                        "individual_position": 10,
                        "company_position": 70,
                        "default_position": 10,
                        "width": 50
                    },
                    "firstname": {
                        "individual_position": 20,
                        "company_position": 80,
                        "default_position": 20,
                        "width": 50
                    },
                    "postcode": {
                        "individual_position": 30,
                        "company_position": 30,
                        "default_position": 100,
                        "width": 50
                    },
                    "city": {
                        "individual_position": 40,
                        "company_position": 40,
                        "default_position": 90,
                        "width": 50
                    },
                    "street": {
                        "individual_position": 50,
                        "company_position": 50,
                        "default_position": 60,
                        "width": 50
                    },
                    "telephone": {
                        "individual_position": 60,
                        "company_position": 60,
                        "default_position": 40,
                        "width": 50
                    },
                    "company": {
                        "individual_position": null,
                        "company_position": 10,
                        "default_position": 30,
                        "width": 50
                    },
                    "vat_id": {
                        "individual_position": null,
                        "company_position": 20,
                        "default_position": 70,
                        "width": 50
                    },
                    "fax": {
                        "individual_position": null,
                        "company_position": null,
                        "default_position": 50,
                        "width": 50
                    },
                    "region": {
                        "individual_position": null,
                        "company_position": null,
                        "default_position": 80,
                        "width": 50
                    },
                    "country_id": {
                        "individual_position": null,
                        "company_position": null,
                        "default_position": 110,
                        "width": 50
                    },
                }
                return addressPositions;
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
                    var parent = document.querySelector('.profile-address-edit__form');
                    var elem = parent.querySelector('.form-group [name*="' + field + '"]').closest('.form-group');

                    if (field == 'street') {
                        elem = parent.querySelector('.form-group.street');
                        this.streetFieldHandler(elem);
                    }

                    switch (formId) {
                        case 'billing-person':
                            this.setOrder(elem, orders.individual_position, orders.width);
                            break;
                        case 'billing-company':
                            this.setOrder(elem, orders.company_position, orders.width);
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
            var formAddressBlock = document.querySelector('.profile-address-edit__form');

            if (elem) {
                var field = elem.querySelector('.form-control');

                if (order !== null) {
                    elem.style.order = order;
                    if (field) field.setAttribute('tabindex', order);
                }

                if (width === 100) elem.classList.add('w-100');

                formAddressBlock.classList.remove('is-loading');
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
    };
});
