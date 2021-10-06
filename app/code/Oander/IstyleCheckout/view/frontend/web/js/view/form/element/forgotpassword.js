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
    options: {
      type: 'popup',
      responsive: true,
      innerScroll: true,
      title: $t('Forgot password'),
      modalClass: 'modal-forgot-password',
      buttons: false,
      recaptchaTargetId: 'mp_recaptcha_forgot',
      recaptchaId: null
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

      if (window.checkoutConfig.mpRecaptcha.forgotPasswordEnabled && !$('#'+this.options.recaptchaTargetId).length) {
        var self = this;
        $('#form-validate.form--forgot-password').append('<div class="g-recaptcha" id=' + self.options.recaptchaTargetId + '></div>');
        grecaptcha.ready(function () {
          var parameters = {
            'sitekey': window.checkoutConfig.mpRecaptcha.sitekey,
            'size': 'invisible',
            'theme': window.checkoutConfig.mpRecaptcha.theme,
            'badge': window.checkoutConfig.mpRecaptcha.position,
            'hl': window.checkoutConfig.mpRecaptcha.language,
          };
          self.options.recaptchaId = grecaptcha.render(self.options.recaptchaTargetId, parameters);
        });
      }

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

      action.on('click', function (e) {
        e.preventDefault();

        if (window.checkoutConfig.mpRecaptcha.forgotPasswordEnabled) {
          grecaptcha.reset(self.options.recaptchaId);
          grecaptcha.execute(self.options.recaptchaId).then(function (token) {
            self.send(email.val(), urlBuilder.build('rest/hu_hu/V1/new_checkout/forgetpassword'));
          });
        } else {
          self.send(email.val(), urlBuilder.build('rest/hu_hu/V1/new_checkout/forgetpassword'));
        }
      });

      email.on('keypress', function (e) {
        if (e.keyCode == 13) {
          e.preventDefault();
        }
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

      if (xhr && xhr.readyState != null) {
        xhr.abort();
      }

      var data = new FormData();
      data.append('customerEmail', email);
      data.append('storeCode', window.checkoutConfig.storeCode);

      if (window.checkoutConfig.mpRecaptcha.forgotPasswordEnabled) {
        data.append('g-recaptcha-response', $('.g-recaptcha-response').val());
      }

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
