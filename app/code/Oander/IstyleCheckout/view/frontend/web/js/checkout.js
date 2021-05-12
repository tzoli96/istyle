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
        $(this).closest(self.defaults.blockCheckoutStep)
          .toggleClass('is-active');
        $(this).closest(self.defaults.blockCheckoutStep)
          .find('.block__content')
          .slideToggle();
      });
    },

    /**
     * Next step
     */
    _nextStep: function () {
      var self = this;
      var actionNextStep = '.action.next-step';

      $(document).on('click', actionNextStep, function () {
        var block = $(this).closest(self.defaults.blockCheckoutStep)
          .find(self.defaults.cardCheckoutStep)
          .find('.card__action');
        var nextBlock = block.closest(self.defaults.blockCheckoutStep)
          .next(self.defaults.blockCheckoutStep)
          .find(self.defaults.cardCheckoutStep)
          .find('.card__action');

        block.trigger('click');
        nextBlock.trigger('click');
      });
    }
  });

  return $.oander.oanderIstyleCheckout;
});
