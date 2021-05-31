define([
  'jquery',
], function ($) {
  'use strict';

  $.widget('oander.oanderIstyleCheckout', {
    defaults: {
      blockCheckoutStep: '.block--checkout-step',
      cardCheckoutStep: '.card--checkout-step-header'
    },

    /**
     * Create
     */
    _create: function () {
      this._blockToggle();
      this._nextStep();
    },

    /**
     * Block toggle
     */
    _blockToggle: function () {
      var self = this;
      var cardAction = self.defaults.cardCheckoutStep + ' .card__action';

      $(document).on('click', cardAction, function () {
        self._stepCounter($(this));
        $(this).closest(self.defaults.blockCheckoutStep)
          .toggleClass('is-active').siblings().removeClass('is-active');
      });
    },

    /**
     * Next step
     */
    _nextStep: function () {
      var self = this;
      var actionNextStep = '.action.next-step';

      $(document).on('click', actionNextStep, function () {
        $(this).closest(self.defaults.blockCheckoutStep)
          .next(self.defaults.blockCheckoutStep)
          .find(self.defaults.cardCheckoutStep + ' .card__action')
          .trigger('click');
      });
    },

    _stepCounter: function (step) {
      var stepData = step.closest('.block--checkout-step').attr('data-step-count');
      var line = $('.block__line').find('.line__information');

      line.css('width', ((stepData * 20) / 2) + '%');
    }
  });

  return $.oander.oanderIstyleCheckout;
});
