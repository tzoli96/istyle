define([
  'jquery',
  'Oander_IstyleCheckout/js/model/store',
], function ($, store) {
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
      this._scrollToNextStep();
    },

    /**
     * Block toggle
     */
    _blockToggle: function () {
      var self = this;
      var cardAction = self.defaults.cardCheckoutStep + ' .card__action';

      $(document).on('click', cardAction, function () {
        store.steps.active($(this).closest(self.defaults.blockCheckoutStep).attr('data-step'));
        self._stepCounter($(this));
        $(this).closest(self.defaults.blockCheckoutStep).toggleClass('is-active').siblings().removeClass('is-active');
        self._animateToActiveSection();
      });
    },

    /**
     * Next step
     */
    _nextStep: function () {
      var self = this;
      var actionNextStep = '.action.next-step';

      $(document).on('click', actionNextStep, function () {
        var stepCount = Number($(this).closest(self.defaults.blockCheckoutStep).attr('data-step-count')) + 1;

        $('.block--checkout-step[data-step-count="'+ stepCount +'"]')
          .find(self.defaults.cardCheckoutStep)
          .find('.card__action')
          .trigger('click');
      });
    },

    /**
     * Animate to active section
     */
    _animateToActiveSection: function() {
      $('html, body').stop().animate({
        scrollTop: $('.block--checkout-step.is-active').offset().top - 100
      }, 400);
    },

    /**
     * ScrollTo Next Step
     */
    _scrollToNextStep: function() {
      var self = this;

      $('body').on('click', '.block--checkout-step button.action.primary', function() {
        self._animateToActiveSection();
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
