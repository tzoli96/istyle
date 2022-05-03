define([
], function () {
    'use strict';

    return {
        /**
         * Sort fields
         * @param {string} formId
         * @returns {void}
         */
        sortFields: function (formId) {
            var self = this;
            var positions = getAddressAttributesPositions;

            if (positions) {
                for (var field in positions) {
                    var orders = positions[field];
                    var parent = document.querySelector('.profile-address-edit__form');
                    var elem = parent.querySelector('.form-group [name*="' + field + '"]').closest('.form-group');

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
         * @param width
         * @returns {void}
         */
        setOrder: function (elem, order, width) {
            var formAddressBlock = document.querySelector('.profile-address-edit__form');

            if (elem) {
                var field = elem.querySelector('.form-control');
                var additionalStreetElem = formAddressBlock.querySelector('.form-group.street_2');
                var additionalStreetField = formAddressBlock.querySelector('.form-group.street_2 .form-control');

                if (order !== null) {
                    elem.style.order = order;

                    if (field) {
                        field.setAttribute('tabindex', order);
                        field.closest('.form-group').style.display = 'block';

                        if (elem.classList.contains('street') && !!additionalStreetElem) {
                            additionalStreetElem.style.order = order + 1;
                            additionalStreetElem.style.display = 'block';
                            additionalStreetField.setAttribute('tabindex',  order + 1);
                        }
                    }
                } else {
                    if (field) {
                        field.closest('.form-group').style.display = 'none';

                        if (field.classList.contains('street') && additionalStreetElem) {
                            additionalStreetElem.style.display = 'none';
                        }
                    }
                }

                if (width === 100) elem.classList.add('w-100');

                document.querySelector('.profile-address-edit').classList.remove('is-loading');
            }
        }
    };
});
