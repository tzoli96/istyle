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
        'Magento_Ui/js/model/messageList'
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

            /**
             * Initialize observable properties
             */
            initObservable: function () {
                this._super()
                    .observe('accountCreated')
                    .observe('isFormVisible')
                    .observe('creationStarted');


                var self = this;
                window.recaptchaOnload = function () {
                    grecaptcha.ready(function() {
                        var target = 'mp_recaptcha_reg',
                            parameters = {
                                'sitekey': self.invisibleKey,
                                'size': 'invisible',
                                'theme': self.theme,
                                'badge': self.position,
                                'hl': self.language
                            };
                        self.recaptchaId = grecaptcha.render(target, parameters);
                    });
                };
                require(['//www.google.com/recaptcha/api.js?onload=recaptchaOnload&render=explicit']);

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

                var self = this;
                grecaptcha.reset(self.recaptchaId);
                grecaptcha.execute(self.recaptchaId).then(function (token) {

                    $.post(
                        self.registrationUrl,
                        $('#registration').serializeArray()
                    ).done(
                        function (response) {

                            if (response.errors == false) {
                                self.accountCreated(true)
                            } else {
                                messageList.addErrorMessage(response);
                            }
                            self.isFormVisible(false);
                        }.bind(self)
                    ).fail(
                        function (response) {
                            self.accountCreated(false)
                            self.isFormVisible(false);
                            messageList.addErrorMessage(response);
                        }.bind(self)
                    );
                });

            },

            /**
             * Enable/disable submit button
             */
            disableSubmit: function() {
                $('#register-agreements .checkbox').on('click', function() {
                    if ($(this).closest('li.item').find('.checkbox').not(':checked').length !== 0 ) {
                        $(this).closest('.checkout-success-registration').find('.create-account .action.primary').attr('disabled','disabled');
                    } else {
                        $(this).closest('.checkout-success-registration').find('.create-account .action.primary').removeAttr('disabled');
                    }
                });
            }
        });
    }
);
