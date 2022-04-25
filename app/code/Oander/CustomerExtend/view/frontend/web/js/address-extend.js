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

    var formObject = {
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
            var submitFormButton = '[data-action="save-address"]';

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

            console.log('isNewAddress', addressId)

            Array.prototype.forEach.call(formElements.titles, function (title) {
                var formId = title.getAttribute('data-tab');
                var isActive = title.parentNode.classList.contains('active');

                if (!addressId) {
                    title.addEventListener('click', function () {
                        Array.prototype.forEach.call(formElements.titles, function (tabTitle) {
                            tabTitle.parentNode.classList.remove('active');
                        });

                        title.parentNode.classList.add('active');
                        formElements.form.setAttribute('data-tab', formId);
                        self.formTransform(formId);

                        if (formId === 'billing-company') {
                            $(formElements.tabs).find('[name="is_company"]').val(1);
                        }
                        else {
                            $(formElements.tabs).find('[name="is_company"]').val(0);
                        }

                        formObject.checkValidatedFields($('.profile-address-edit__form'));
                    });
                }

                if (isActive) self.watchSpecificFields(formId);
            });
        },

        /**
         * Watch specific fields
         * @return {Void}
         */
        watchSpecificFields: function (formId) {
            var self = this;

            var watch = setInterval(function () {
                var formElements = self.formElements();
                if (formElements.companyField || formElements.vatIdField) {
                    self.formTransform(formId);
                    clearInterval(watch);
                }
            }, 1000);
        },

        /**
         * Form changes
         * @return {Void}
         */
        formChanges: function () {
            var self = this;
            var formElements = this.formElements();

            this.tabs();
        },

        /**
         * Validate shipping fields
         * @param {HTMLElement} form
         * @returns {Void}
         */
        validateShippingFields: function (form) {
            var self = this;

            if (form.hasClass('form-shipping-address')) self.watchRequiredFields(form);

            if (form) {
                var fields = form.find('.form-group');


                fields.each(function (index, field) {
                    var fieldElement = $(field).find('.form-control');

                    fieldElement.on('keyup change', function () {
                        self.classHandler($(this));
                    });

                    self.classHandler(fieldElement);
                });
            }
        },

        /**
         * Form transform
         * @param {String} formId
         * @return {Void}
         */
        formTransform: function (formId) {
            // billingAddressStore.fieldsContent({});
            // billingAddressValidate.mainFields = {};

            switch (formId) {
                case 'billing-person':
                    this.formPerson();
                    break;
                case 'billing-company':
                    this.formCompany();
                    break;
            }

            sort.sortFields(formId);
        },

        /**
         * Form person
         * @return {Void}
         */
        formPerson: function () {
            var formElements = this.formElements();

            $(formElements.companyField).closest('.form-group').hide();
            $(formElements.companyField).removeClass('_required');
            $(formElements.vatIdField).closest('.form-group').hide();
            if ($(formElements.vatIdField).hasClass('vat-required')) $(formElements.vatIdField).removeClass('_required');

            if ($(formElements.pfpjField).length) {
                $(formElements.pfpjField).closest('.form-group').hide();
                $(formElements.pfpjField).removeClass('_required');
            }

            $(formElements.form).find('.field-name-firstname > .label').text($t('First Name'));
            $(formElements.form).find('.field-name-lastname > .label').text($t('Last Name'));
        },

        /**
         * Form company
         * @return {Void}
         */
        formCompany: function () {
            var formElements = this.formElements();

            $(formElements.companyField).closest('.form-group').show();
            $(formElements.companyField).addClass('_required');
            $(formElements.vatIdField).closest('.form-group').show();
            if ($(formElements.vatIdField).hasClass('vat-required')) $(formElements.vatIdField).addClass('_required');

            if ($(formElements.pfpjField).length) {
                $(formElements.pfpjField).closest('.form-group').show();
                $(formElements.pfpjField).addClass('_required');
            }

            $(formElements.form).find('.field-name-firstname > .label').text($t('Contact person firstname'));
            $(formElements.form).find('.field-name-lastname > .label').text($t('Contact person lastname'));

            this.fieldErrorHandling($(formElements.companyField));
            if ($(formElements.vatIdField).hasClass('vat-required')) {
                this.fieldErrorHandling($(formElements.vatIdField));
                this.fieldErrorHandling($(formElements.pfpjField));
            }
        },
        /**
         * Field error handling
         * @return {Void}
         */
        fieldErrorHandling: function (field) {
            if (!field.find('.mage-error').length) {
                field.append('<div class="mage-error error_on_field d-none">' + $t('Required fields') + '<//div>');
            }

            field.find('.form-control').on('keyup', function () {
                if (!$(this).val().length) {
                    if (!field.find('.mage-error:not(.error_on_field)').length) {
                        field.addClass('_error');
                        field.find('.error_on_field').removeClass('d-none');
                    }
                } else {
                    field.find('.error_on_field').addClass('d-none');
                    if (!field.find('.mage-error:not(.error_on_field)').length) {
                        field.removeClass('_error');
                    }
                }
            });
        },
        // TODO validate
        /**
         * Check validated fields
         * @param {HTMLElement} form
         * @returns {Void}
         */
        checkValidatedFields: function (form) {
            var self = this;

            if (form) {
                var fields = form.find('.form-group._required, .form-group.true, .oandervalidate-length, .oandervalidate-regex');

                fields.each(function (index, field) {
                    if (self.isVisibleInDom($(field))) {
                        var fieldElement = $(field).find('.form-control');

                        fieldElement.on('keyup change', function () {
                            self.requiredHandler($(this), fieldElement.attr('name'));
                        });

                        self.requiredHandler(fieldElement, fieldElement.attr('name'));
                    }
                });
            }
        },

        /**
         * Required handler
         * @param {HTMLElement} element
         * @param {String} key
         * @returns {Void}
         */
        requiredHandler: function (element, key) {
            var mainFields = {};
            var self = this;

            if ($(element).length) {
                if (self.isVisibleInDom($(element).closest('.form-group'))) {
                    delete mainFields[key];

                    if ($(element).closest('.form-group').hasClass('_error')) {
                        mainFields[key] = false;
                    } else if (($(element).closest('.form-group').hasClass('_required') || $(element).closest('.form-group').hasClass('vat_required')) && !$(element).val().length) {
                        mainFields[key] = false;
                    } else {
                        mainFields[key] = true;
                    }
                }
            }

            billingAddressStore.fieldsContent(this.mainFields);
            this.checkRequiredFields();
        },

        /**
         * Check required fields
         * @returns {Void}
         */
        checkRequiredFields: function () {
            var fields = billingAddressStore.fieldsContent();
            var fieldsLength = 0;
            var validatedFieldsCount = 0;

            for (var field in fields) {
                fieldsLength++;
                if (fields[field]) validatedFieldsCount++;
            }

            if (fieldsLength === validatedFieldsCount) store.billingAddress.continueBtn(true);
            else store.billingAddress.continueBtn(false);
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
        // TODO validate ends
    };
    formObject.formChanges();
});
