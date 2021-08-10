define([
  'jquery',
  'ko',
  'Magento_Ui/js/modal/modal',
  'mage/translate',
  'mage/url',
  'mage/validation',
], function ($, ko, modal, $t, urlBuilder) {
  'use strict';

  var forgotPassword = {
    emailFieldStatus: ko.observable(false),
    emailMessage: ko.observable(false),
    popup: '',
    recaptchaId: '',
    options: {
      type: 'popup',
      responsive: true,
      innerScroll: true,
      title: $t('Forgot password'),
      modalClass: 'modal-forgot-password',
      buttons: false
    },

    /**
     * Open modal
     * @returns {Void}
     */
    openModal: function () {
      var block = $('.block--forgot-password');
      block.removeClass('d-none');

      this.popup = modal(this.options, block);

      block.modal('openModal');

      this.form();
    },

    recaptcha: function () {
      var self = this;

      window.recaptchaOnload = function () {
        grecaptcha.ready(function () {
          var recaptchaBlock = setInterval(function () {
            if ($('#mp_recaptcha_forgot_password').length) {
              var target = 'mp_recaptcha_forgot_password';
              var parameters = {
                'sitekey': window.checkoutConfig.istyle_checkout.get_invisible_key,
                'size': 'invisible',
                'theme': window.checkoutConfig.istyle_checkout.get_theme_frontend,
                'badge': window.checkoutConfig.istyle_checkout.get_position_frontend,
                'hl': window.checkoutConfig.istyle_checkout.get_language_code
              };

              self.recaptchaId = grecaptcha.render(target, parameters);

              clearInterval(recaptchaBlock);
            }
          }, 500);
        });
      }

      require(['//www.google.com/recaptcha/api.js?onload=recaptchaOnload&render=explicit']);
    },

    /**
     * Form
     * @returns {Void}
     */
    form: function () {
      var self = this;
      var form = $('.form--forgot-password');
      var action = form.find('.action');
      var email = form.find('[name="forgot-email"]');

      self.validateField(email);

      action.on('click', function () {
        self.send(email.val(), urlBuilder.build('rest/hu_hu/V1/new_checkout/forgetpassword'));
      });
    },

    /**
     * Validate
     * @param {HTMLElement} field
     * @returns {Void}
     */
    validateField: function (field) {
      var self = this;
      var form = $('.form--forgot-password');
      var validator;

      form.validation();

      validator = form.validate();

      if (field.val().length > 0) {
        self.emailFieldStatus(validator.check(field));
        field.valid();
      }
      else {
        self.emailFieldStatus(false);
        !field.valid();
      }

      field.on('keyup change', function () {
        if ($(this).val().length > 0) {
          self.emailFieldStatus(validator.check($(this)));
          field.valid();
        }
        else {
          self.emailFieldStatus(false);
          !field.valid();
        }
      });
    },

    /**
     * Send
     * @param {String} email
     * @param {String} url
     * @returns {Void}
     */
    send: function (email, url) {
      var self = this;
      var block = $('.block--forgot-password');
      var msg;

      grecaptcha.reset(self.recaptchaId);
      grecaptcha.execute(self.recaptchaId).then(function (token) {
        if (xhr && xhr.readyState != null) {
          xhr.abort();
        }
      });

      var data = new FormData();
      data.append('customerEmail', email);

      self.emailMessage(false);

      var xhr = $.ajax({
        type: 'POST',
        url: url,
        data: data,
        processData: false,
        contentType: false,
        showLoader: true,
      }).done(function () {
        msg = $t('If there is an account associated with %1 you will receive an email with a link to reset your password.').replace('%1', email);
        self.emailMessage(msg);
        block.modal('closeModal');
      });
    },
  };

  return forgotPassword;
});
