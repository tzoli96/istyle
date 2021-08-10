/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'uiComponent',
        'Magento_Ui/js/model/messageList',
        'mage/validation',
        'passwordStrengthIndicator'
    ],
    function ($, Component, messageList) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/registration',
                accountCreated: false,
                creationStarted: false,
                isFormVisible: true
            },

            /**
             * Initialize observable properties
             */
            initObservable: function () {
                this._super()
                    .observe('accountCreated')
                    .observe('isFormVisible')
                    .observe('creationStarted');

                return this;
            },

            /**
             * @return {*}
             */
            getEmailAddress: function () {
                return this.email;
            },

            /**
             * Create new user account
             */
            createAccount: function () {
                this.creationStarted(true);
                $.post(
                    this.registrationUrl,
                    $('#registration').serializeArray()
                ).done(
                    function (response) {

                        if (response.errors == false) {
                            this.accountCreated(true)
                        } else {
                            messageList.addErrorMessage(response);
                        }
                        this.isFormVisible(false);
                    }.bind(this)
                ).fail(
                    function (response) {
                        this.accountCreated(false)
                        this.isFormVisible(false);
                        messageList.addErrorMessage(response);
                    }.bind(this)
                );
            },

            /**
             * Enable/disable submit button
             */
            disableSubmit: function() {
                this.validateField($('#password'));

                $('#register-agreements .checkbox').on('click', function() {
                    if ($(this).closest('li.item').find('.checkbox').not(':checked').length !== 0 ) {
                        $(this).closest('.checkout-success-registration').find('.create-account .action.primary').attr('disabled','disabled');
                    } else {
                        $(this).closest('.checkout-success-registration').find('.create-account .action.primary').removeAttr('disabled');
                    }
                });
            },

            /**
             * Validate
             * @param {HTMLElement} field
             * @returns {Void}
             */
            validateField: function (field) {
                var form = $('#registration'),
                    validator;
        
                form.validation();
                validator = form.validate();
        
                field.on('keyup change', function () {
                    if ($(this).val().length > 0) {
                        console.log(validator.check($(this)));
                        field.valid();
                    } else {
                        !field.valid();
                    }
                });
            },
        });
    }
);
