define([
    "jquery",
    "mage/mage",
    'mage/translate',
    'Oander_CustomerExtend/js/address-extend/sort',
], function (
    $,
    ko,
    $t,
    sort
    ){
    'use strict';

    var addressExtend = {
        /**
         * Form elements
         * @return {Object}
         */
        formElements: function () {
            var form = document.querySelector('.profile-address-edit');
            var tabs = document.querySelector('.tab.tab--profile');
            var titles = tabs.querySelectorAll('.tab__switch');
            var companyField = form.querySelector('[name="company"]');
            var vatIdField = form.querySelector('[name="vat_id"]');
            var pfpjField = form.querySelector('[name="custom_attributes.pfpj_reg_no"]');
            var submitFormButton = form.querySelector('[data-action="save-address"]');

            return {
                form: form,
                tabs: tabs,
                titles: titles,
                companyField: companyField,
                vatIdField: vatIdField,
                pfpjField: pfpjField,
                submitFormButton: submitFormButton
            }
        },

        /**
         * Tabs
         * @return {Void}
         */
        tabs: function () {
            var self = this;
            var formElements = this.formElements();
            var addressId = $(formElements.tabs).attr('data-address-id');
            var isCompanyValue = $(formElements.tabs).find('[name="is_company"]').val();
            var isActiveTab = formElements.titles[isCompanyValue].getAttribute('data-tab');

            Array.prototype.forEach.call(formElements.titles, function (title, index) {
                var formId = title.getAttribute('data-tab');

                if (isCompanyValue == index ) {
                    title.parentNode.classList.add('active');
                } else {
                    title.parentNode.classList.remove('active');
                }

                if (!addressId) {
                    title.addEventListener('click', function () {
                        Array.prototype.forEach.call(formElements.titles, function (tabTitle) {
                            tabTitle.parentNode.classList.remove('active');
                        });

                        title.parentNode.classList.add('active');
                        formElements.form.setAttribute('data-tab', formId);
                        sort.sortFields(formId);

                        if (formId === 'billing-company') {
                            $(isCompanyValue).val(1);
                        }
                        else {
                            $(isCompanyValue).val(0);
                        }
                    });
                }
            });

            sort.sortFields(isActiveTab);

            formElements.submitFormButton.addEventListener('click', function () {
                addressExtend.checkValidatedFields($('.profile-address-edit__form'));
            });
        },
        /**
         * Form person
         * @return {Void}
         */
        formPerson: function () {
            var formElements = this.formElements();

            $(formElements.form).find('[name="billingAddressshared.firstname"] > .label').text($t('First Name'));
            $(formElements.form).find('[name="billingAddressshared.lastname"] > .label').text($t('Last Name'));
        },

        /**
         * Form company
         * @return {Void}
         */
        formCompany: function () {
            var formElements = this.formElements();

            $(formElements.form).find('[name="billingAddressshared.firstname"] > .label').text($t('Contact person firstname'));
            $(formElements.form).find('[name="billingAddressshared.lastname"] > .label').text($t('Contact person lastname'));
        },

        /**
         * Check validated fields
         * @param {HTMLElement} form
         * @returns {Void}
         */
        checkValidatedFields: function (form) {
            var self = this;

            setTimeout(function () {
                if (form) {
                    var fields = form.find('.form-group.required, .form-group._required, .form-group.true, .oandervalidate-length, .oandervalidate-regex');

                    fields.each(function (field) {
                        if (self.isVisibleInDom($(field))) {
                            if (!($(this).find('select[disabled], input[disabled], .mage-error').length)) {
                                $(this).addClass('filled');
                            }
                        }
                    });
                }
            }, 100)
        },

        /**
         * Is visible in DOM
         * @param {HTMLElement} elem
         * @returns {Boolean}
         */
        isVisibleInDom: function (elem) {
            var style = elem.attr('style');
            if (style) {
                if (style.indexOf('display: none') > -1) return false;
                return true;
            }
            return true;
        }
    };
    addressExtend.tabs();
});
