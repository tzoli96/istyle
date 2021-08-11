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
                isFormVisible: true,
                recaptchaId: null
            },
            recaptcha: {
                formSelector: '#registration.checkout-success-registration',
                targetId: 'mp_recaptcha_reg',
                renderId: null,
            },

            /**
             * Initialize observable properties
             */
            initObservable: function () {
                this._super()
                    .observe('accountCreated')
                    .observe('isFormVisible')
                    .observe('creationStarted');


                if (this.isRecaptchaEnabled && !$('#'+this.recaptcha.targetId).length) {
                    var self = this;
                    window.recaptchaOnload = function () {
                        $(self.recaptcha.formSelector).append('<div class="g-recaptcha" id=' + self.recaptcha.targetId + '></div>');
                        grecaptcha.ready(function () {
                            var parameters = {
                                'sitekey': self.invisibleKey,
                                'size': 'invisible',
                                'theme': self.theme,
                                'badge': self.position,
                                'hl': self.language
                            };
                            self.recaptcha.renderId = grecaptcha.render(self.recaptcha.targetId, parameters);
                        });
                    };
                    require(['//www.google.com/recaptcha/api.js?onload=recaptchaOnload&render=explicit']);
                }

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

                if (this.isRecaptchaEnabled) {
                    var self = this;
                    grecaptcha.reset(self.recaptcha.renderId);
                    grecaptcha.execute(self.recaptcha.renderId).then(function (token) {
                        self.createAccountAjax();
                    });
                } else {
                    this.createAccountAjax();
                }
            },

            createAccountAjax: function () {
                $.post(
                    this.registrationUrl,
                    $(this.recaptcha.formSelector).serializeArray()
                ).done(
                    function (response) {
                        if (response.errors == false) {
                            $('.account-created').text(response.message)
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
