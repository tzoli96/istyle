define([
  'jquery',
  'Magento_Catalog/js/price-utils',
  'mage/translate',
], function ($, priceUtils, $t) {
  'use strict';

  $.widget('oander.oanderHelloBank', {
    classes: {
      tab: '.data.switch[href="#hellobank"]',
      calculator: '.hellobank-calculator',
      loader: '.calculator-loader',
      priceBox: '.product-info-price [data-role=priceBox]',
    },

    vars: {
      bundleCalculator: 0,
      bundlePrice: 0,
    },
    /**
     * Init
     */
    _init: function () {
      this._tabs();
      this._range();
      this._getInsurances();

      if (this.options.page == "product") {
        if (this.options.productType == 'configurable') {
          this._config();
        }
        else if (this.options.productType == 'bundle') {
          this._bundle();
        }
        else if (this.options.productType == 'simple') {
          this._actionCalculator();
        }
      }
      else if (this.options.page == "checkout") {
        this._actionCalculator();
      }
    },

    /**
     * Urls
     * 
     * @return object
     */
    _urls: function () {
      return {
        infoUrl: 'https://www.cetelem.cz/webciselnik2.php?kodProdejce=' + this.options.sellerId,
        calculatorUrl: 'https://www.cetelem.cz/webkalkulator.php?kodProdejce=' + this.options.sellerId,
      };
    },

    /**
     * Tabs
     */
    _tabs: function () {
      var self = this;
      var tabs = $('.tabs--calculator');
      var title = tabs.find('.tabs__title');
      var content = tabs.find('.tabs__content');

      title.click(function () {
        self._triggerInsurance($(this).attr('data-tab-index'));

        $(this).addClass('active').siblings().removeClass('active');

        content.removeClass('active');
        tabs.find('.tabs__content[data-tab-index="' + $(this).attr('data-tab-index') + '"]')
          .addClass('active')
          .trigger('click');
        tabs.find('.tabs__content.active')
          .find('.action-calculator')
          .trigger('click');
      });
    },

    /**
     * Get insurances
     */
    _getInsurances: function () {
      var self = this;

      if (xhr && xhr.readyState != null) xhr.abort();

      var xhr = $.ajax({
        url: this._urls().infoUrl + '&typ=pojisteni',
        type: 'GET',
        dataType: 'xml'
      }).done(function (data) {
        $(data).find('pojisteni').each(function () {
          var id = $(this).attr('id');
          var title = $(this).find('titul').text();
          var content = $(this).find('napoveda').text();

          self._setInsurances(id, title, content);

          $(self.classes.calculator)
            .find('.tabs__title.active')
            .trigger('click');
        });
      });
    },

    /**
     * Set insurances
     *
     * @param {*} id
     * @param {*} title
     * @param {*} content
     */
    _setInsurances: function (id, title, content) {
      var tab = $('.tabs__content');
      var insurances = tab.find('.insurances').find('.row');

      insurances.append(
        $('<div/>')
          .attr('data-id', id)
          .addClass('col-6 insurance')
          .append(
            $('<input type="radio"/>')
              .attr('id', id)
              .attr('name', 'insurance')
              .attr('value', id))
          .append(
            $('<label/>')
              .attr('for', id)
              .text(id)
              .append(
                $('<span/>')
                  .addClass('info')
                  .append(
                    $('<span/>')
                      .addClass('tooltip')
                      .html('<b>' + title + '</b><p>' + content + '</p>')
                  )
              )
          )
      );
    },

    /**
     * Trigger first insurance
     */
    _triggerInsurance: function (index) {
      var label = $('.tabs__content[data-tab-index="' + index + '"]')
        .find('.insurance:nth-child(2) > label')
        .attr('for');

      $('.tabs__content[data-tab-index="' + index + '"]').find('#' + label).attr('checked', 'checked');

      $('.tabs__content[data-tab-index="' + index + '"] input[name="insurance"]').on('change', function () {
        var currentTab = $(this).closest('.tabs__content.active');
        currentTab.find('.action-calculator').trigger('click');
      });
    },

    /**
     * Get formatted price
     * 
     * @param {*} price 
     * @returns float
     */
    _getFormattedPrice: function (price) {
      var priceFormat = {
        decimalSymbol: '.',
        groupLength: 3,
        groupSymbol: ",",
        integerRequired: false,
        pattern: "$%s",
        precision: 2,
        requiredPrecision: 2
      };

      return priceUtils.formatPrice(price, priceFormat);
    },

    /**
     * Range
     */
    _range: function () {
      var range = $('.calculator-range-wrapper').find('.slider');

      range.on('change', function () {
        var currentTab = $(this).closest('.tabs__content.active');
        var currentCalculator = currentTab.find('.calculator-range-wrapper');
        var stepValue = currentCalculator.find('.slider').val();
        var steps = currentCalculator.find('span');

        steps.removeClass('active');
        $(steps[stepValue]).addClass('active');

        currentTab.find('.form__installment.months').text($(steps[stepValue]).attr('data-title'));
        currentTab.find('.action-calculator').trigger('click');
      });
    },

    /**
     * Action calculator
     */
    _actionCalculator: function (price, type) {
      var self = this;
      var calculator = $('.action-calculator');

      calculator.on('click', function () {
        var recentPrice = price ? price : self.options.price;
        var content = $('.tabs__content.active');
        var downpayment = content.find('input[name="down-payment"]');

        if (type == 'bundle') {
          recentPrice = self.vars.bundlePrice;
        }

        downpayment.trigger('keyup');

        var data = {
          barem: content.attr('data-barem-id'),
          insurance: content.find('input[name="insurance"]:checked').val(),
          productPrice: recentPrice,
          loanPrice: (downpayment.val()) ? (recentPrice - downpayment.val()) : recentPrice,
          downpayment: downpayment.val(),
          month: content.find('.form__installment.months').text(),
        }

        self._getCalculate(data);
      });
    },

    /**
     * Check downpayment
     * @param {*} baremId 
     * @param {*} downpayment 
     * @param {*} price 
     * @returns object
     */
    _checkDownPayment: function (baremId, downpayment, price) {
      var downpayment = parseInt(downpayment);
      var barem = $(this.classes.calculator).find('.tabs__content[data-barem-id="' + baremId + '"]');
      var loan = barem.find('.form-control[name="down-payment"]');
      var loanMin;

      var baremMin = parseInt(barem.attr('data-barem-min'));
      var baremMax = parseInt(barem.attr('data-barem-max'));

      if (loan.hasClass('down-payment-min')) {
        loanMin = parseInt(loan.attr('data-down-payment-min'));
      }
      else {
        loanMin = (parseInt(price) - baremMax);
      }

      var loanMax = (parseInt(price) - baremMin);

      if (downpayment < loanMin) {
        return {
          status: 0,
          message: $t('Minimum downpayment:'),
          price: this._getFormattedPrice(loanMin),
        }
      }
      else if (downpayment > loanMax) {
        return {
          status: 0,
          message: $t('Maximum downpayment:'),
          price: this._getFormattedPrice(loanMax),
        }
      }
      else {
        return {
          status: 1,
        }
      }
    },

    /**
     * Get calculate
     * 
     * @param {*} data 
     */
    _getCalculate: function (data) {
      var self = this;
      var xhrReady = true;

      if (data.downpayment) {
        var check = this._checkDownPayment(data.barem, data.downpayment, data.productPrice);

        if (check.status == 0) {
          xhrReady = false;

          $(self.classes.calculator)
            .find('.tabs__content[data-barem-id="' + data.barem + '"]')
            .find('.alert')
            .removeClass('d-none')
            .html($t(check.message) + ' ' + check.price);
        }
        else {
          xhrReady = true;
        }
      }

      if (xhrReady) {
        if (xhr && xhr.readyState != null) xhr.abort(); $(this.classes.calculator).find(this.classes.loader).show();

        var xhr = $.ajax({
          url: this._urls().calculatorUrl,
          type: 'GET',
          data: {
            kodBaremu: data.barem,
            kodPojisteni: data.insurance,
            cenaZbozi: data.productPrice,
            vyseUveru: data.loanPrice,
            primaPlatba: (data.downpayment) ? data.downpayment : 0,
            pocetSplatek: data.month,
          },
          dataType: 'xml'
        }).done(function (res) {
          if (self.options.page == 'checkout') {
            window.calculatedData = res;
          }
          self._setCalculateResponse(res);
          $(self.classes.calculator).find(self.classes.loader).hide();
          $(self.classes.tab).removeClass('d-none');
          $(self.classes.calculator).removeClass('d-none');

          $(self.classes.calculator)
            .find('.tabs__content[data-barem-id="' + data.barem + '"]')
            .find('.alert')
            .addClass('d-none')
        });
      }
    },

    /**
     * Set calculator response
     * 
     * @param {*} res 
     */
    _setCalculateResponse: function (res) {
      var status = $(res).find('status').text();
      var ok = false;

      if (status == 'ok') ok = true;

      if (ok) {
        var result = $(this.classes.calculator)
          .find('.tabs__content.active')
          .find('.catalog-calculator');

        if (this.options.page == 'checkout') {
          var result = $(this.classes.calculator)
            .find('.tabs__content.active')
            .find('.checkout-calculator');
        }

        var values = $(res).find('vysledek');
        var value = {
          month: values.find('pocetSplatek').text(),
          installment: values.find('vyseSplatky').text(),
          installmentWithoutInsurance: values.find('vyseSplatkyBezPojisteni').text(),
          priceOfCredit: values.find('cenaUveru').text(),
          thm: values.find('RPSN').text(),
          interestRate: values.find('ursaz').text(),
          totalAmount: values.find('celkovaCastka').text(),
        };

        result.find('.form__installment.months').html(value.month);
        result.find('.total-payable > .price').html(this._getFormattedPrice(value.totalAmount));
        result.find('.monthly-instalment > .price').html(this._getFormattedPrice(value.installment));
        result.find('.thm > .value').html(value.thm);
        result.find('.interest-rate > .value').html(value.interestRate);
        result.find('.handling-fee > .price').html(this._getFormattedPrice(value.priceOfCredit));
      }
    },

    /**
     * Bundle
     */
    _bundle: function () {
      var self = this;
      var option = $('.fieldset-bundle-options').find('.field > .label');
      var init = 0;
      var finalPrice;

      if (!init) {
        $('.price-wrapper[data-price-type="finalPrice"]').bind('DOMSubtreeModified', function () {
          init = 1;
        });
      }

      setTimeout(function () {
        if (init) {
          finalPrice = Math.round($(self.classes.priceBox).data('mage-priceBox').cache.displayPrices.finalPrice.amount);

          self.vars.bundlePrice = finalPrice;
          self._bundleRefresh();
        }
      }, 1000);

      option.on('click', function () {
        finalPrice = Math.round($(self.classes.priceBox).data('mage-priceBox').cache.displayPrices.finalPrice.amount);

        $(self.classes.priceBox).on('updatePrice', function () {
          var priceBox = $(self.classes.priceBox).data('mage-priceBox').cache.displayPrices;

          if (!$.isEmptyObject(priceBox)) {
            if (priceBox.finalPrice.final != null) {
              finalPrice = Math.round(priceBox.finalPrice.final);
            } else {
              finalPrice = Math.round(priceBox.finalPrice.amount);
            }
          }
        });

        setTimeout(function () {
          self.vars.bundlePrice = finalPrice;
          self._bundleRefresh();
        }, 1000);
      });
    },

    /**
     * Bundle refresh
     */
    _bundleRefresh: function () {
      var self = this;
      var data = $('.tabs__content');
      var finalPrice = this.vars.bundlePrice;

      $(self.classes.calculator).find('.form__value > .price').text(finalPrice);

      $(data).each(function () {
        var barem = $(this);
        var id = barem.attr('data-barem-id');
        var min = barem.attr('data-barem-min');
        var max = barem.attr('data-barem-max');
        var equity = barem.attr('data-barem-equity');
        var loan = barem.find('.form-control[name="down-payment"]');
        var minLoan = (finalPrice - max);

        if (min <= finalPrice) {
          barem.removeClass('d-none');
          $('.tabs__title[data-barem-id="' + id + '"]').removeClass('d-none');
        }
        else {
          barem.addClass('d-none');
          $('.tabs__title[data-barem-id="' + id + '"]').addClass('d-none');
        }

        if (parseInt(equity) >= 0) {
          if (max < finalPrice) {
            barem.addClass('d-none');
            $('.tabs__title[data-barem-id="' + id + '"]').addClass('d-none');
          }
        }

        if (min <= finalPrice && max >= finalPrice) {
          loan.removeClass('down-payment-calc');
          loan.addClass('down-payment-min');
          loan.val(0);
          loan.attr('data-down-payment-min', 0);
        }
        else if (finalPrice > max) {
          loan.removeClass('down-payment-min');
          loan.addClass('down-payment-calc');
          loan.val(minLoan);
          loan.removeAttr('data-down-payment-min');
        }
      });

      $(self.classes.calculator)
        .find('.tabs__title:not(.d-none)')
        .first()
        .addClass('active');

      $(self.classes.calculator)
        .find('.tabs__content:not(.d-none)')
        .first()
        .addClass('active');

      if (!self.vars.bundleCalculator) {
        self._actionCalculator('', 'bundle');
        self.vars.bundleCalculator = 1;
      }

      $(self.classes.calculator)
        .find('.tabs__title.active')
        .trigger('click');
    },

    /**
     * Update config
     */
    _config: function () {
      var self = this;
      $(this.classes.priceBox).on('updatePrice', function () {
        var productId = $('.swatch-option.selected').attr('data-product-id');
        var priceBox = $(self.classes.priceBox).data('mage-priceBox').cache.displayPrices;
        var finalPrice;

        if (!$.isEmptyObject(priceBox)) {
          if (priceBox.finalPrice.final != null) {
            finalPrice = Math.round(priceBox.finalPrice.final);
          } else {
            finalPrice = Math.round(priceBox.finalPrice.amount);
          }
        }

        var watchProductId = setInterval(function () {
          if (productId != '') {
            self._renderCalculatorByProductId(productId, finalPrice);
            clearInterval(watchProductId);
          }
        }, 1000);
      });
    },

    /**
     * Render calculator by product id
     * @param {*} id 
     */
    _renderCalculatorByProductId: function (id, price) {
      var self = this;
      var data = this.options.config[id] ? this.options.config[id] : [];
      var filteredData = [];
      var tabs = $('.tabs--calculator');
      var titles = tabs.find('.tabs__titles');
      var contents = tabs.find('.tabs__contents');
      var finalPrice = price;

      $(data).each(function (key, value) {
        if (parseInt(value.equity) >= 0) {
          if (value.max_price >= price) {
            filteredData.push(value);
          }
        }
        else {
          filteredData.push(value);
        }
      });

      var sortedData = filteredData.sort(function (a, b) {
        return a.priority - b.priority;
      });

      titles.html(''); contents.html('');

      $(sortedData).each(function (key, value) {
        self._renderTitles(key, value, finalPrice);
        self._renderContents(key, value, finalPrice);
      });

      if (titles.find('.tabs__title[data-tab-index="0"]').length <= 0) {
        titles.find('.tabs__title').first().addClass('active');
        contents.find('.tabs__content').first().addClass('active');
      }

      $(self.classes.tab).addClass('d-none');
      $(self.classes.calculator).addClass('d-none');

      if (titles.find('.tabs__title').length > 0) {
        $('.data.item.title:not([aria-controls="hellobank"])').removeClass('active');
        $('.data.item.content:not(.hellobank-calculator)').hide();

        $(self.classes.tab).removeClass('d-none');
        $(self.classes.calculator).removeClass('d-none');

        $(self.classes.tab).closest('.data.item.title').addClass('active');
        $(self.classes.calculator).show();
      }
      else {
        if ($('.data.item.title:not([aria-controls="hellobank"])').length > 0) {
          $('.data.item.title:not([aria-controls="hellobank"])').first().find('.data.switch').trigger('click');
        }
      }

      self._tabs();
      self._range();
      self._actionCalculator(finalPrice);

      $(self.classes.calculator)
        .find('.tabs__title.active')
        .trigger('click');
    },

    /**
     * Render titles
     */
    _renderTitles: function (key, data, price) {
      var tabs = $('.tabs--calculator');
      var titles = tabs.find('.tabs__titles');

      if (data.min_price <= price) {
        titles.append(
          $('<div/>')
            .addClass('tabs__title' + (key == 0 ? ' active' : ''))
            .attr('data-tab-index', key)
            .attr('data-barem-id', data.barem_id)
            .html(data.name)
        );
      }
    },

    /**
     * Render contents
     */
    _renderContents: function (key, data, price) {
      var tabs = $('.tabs--calculator');
      var contents = tabs.find('.tabs__contents');
      var content = tabs.find('.tabs__content.d-none').clone();
      var range = content.find('.calculator-range');
      var installments = data.installments.split(',');
      var loan = content.find('.form-control[name="down-payment"]');
      var minLoan = (price - data.max_price);

      if (key == 0) content.addClass('active');
      content.attr('data-tab-index', key);
      content.attr('data-barem-id', data.barem_id);
      content.attr('data-barem-min', data.min_price);
      content.attr('data-barem-max', data.max_price);
      content.find('.form__value.amount > .price').html(this._getFormattedPrice(price));
      content.find('.form__installment.months').html(data.default_installment);
      loan.removeClass('down-payment-min');
      loan.removeClass('down-payment-calc');

      if (parseInt(data.equity) == "0") {
        loan.val('');
        content.find('.form__label.down-payment').hide();
        content.find('.col-dp').hide();
        content.find('.col-dp-btn').removeClass('col-sm-6').addClass('col-sm-12 d-none');
      }
      else if (parseInt(data.equity) > 0) {
        loan.addClass('down-payment-min');
        loan.attr('data-down-payment-min', data.equity);
        loan.val(data.equity);
      }
      else {
        if (data.min_price <= price && data.max_price >= price) {
          loan.val(0);
          loan.addClass('down-payment-min');
          loan.attr('data-down-payment-min', 0);
        }
        else if (price > data.max_price) {
          loan.addClass('down-payment-calc');
          loan.val(minLoan);
        }
      }

      if (data.installments_type != 3) {
        content.find('.col-range').hide();
      }
      else {
        var months = 0;
        range.append(
          $('<div/>')
            .addClass('calculator-steps')
        );
        $(installments).each(function (key, value) {
          range.find('.calculator-steps').append(
            $('<span/>')
              .attr('data-title', value.trim())
          );
          months++;
        });
        range.append(
          $('<input/>')
            .attr('type', 'range')
            .attr('min', 0)
            .attr('max', (months - 1))
            .val(0)
            .addClass('slider')
        );
        range.find('[data-title="' + data.default_installment + '"]').addClass('active');
      }

      if (data.min_price <= price) {
        contents.append(content.removeClass('d-none'));
      }
    }
  });

  return $.oander.oanderHelloBank;
});
