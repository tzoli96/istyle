define([
  'jquery',
  'Magento_Catalog/js/price-utils',
  'jquery/ui',
], function ($, priceUtils) {
  'use strict';

  $.widget('oander.oanderHelloBank', {
    classes: {
      tab: '.data.switch[href="#hellobank"]',
      calculator: '#hellobank',
      loader: '.calculator-loader',
    },
    /**
     * Init
     */
    _init: function () {
      this._tabs();
      this._range();
      this._getInsurances();

      if (this.options.productType == 'configurable') {
        this._config();
      }
      else if (this.options.productType == 'bundle') {
        this._bundleCheck();
      }
      else if (this.options.productType == 'simple') {
        this._actionCalculator();
      }
    },

    /**
     * Config init
     */
    _config: function () {
      this._updateConfig();
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
        self._triggerFirstInsurance($(this).attr('data-tab-index'));

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
      var insurances = $('.insurances').find('.row');

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
    _triggerFirstInsurance: function (index) {
      var label = $('.tabs__content[data-tab-index="' + index + '"]')
        .find('.insurance:nth-child(1) > label')
        .attr('for');

      $('.tabs__content[data-tab-index="' + index + '"]').find('#' + label).click();
    },

    /**
     * Get price
     */
    _getPrice: function () {
      var productType = this.options.productType;

      if (productType == 'simple') {
        return this.options.price;
      }
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
    _actionCalculator: function (price) {
      var self = this;
      var calculator = $('.action-calculator');
      var recentPrice = price ? price : self._getPrice();

      calculator.on('click', function () {
        var content = $('.tabs__content.active');
        var downpayment = content.find('input[name="down-payment"]');

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
     * Get calculate
     * 
     * @param {*} data 
     */
    _getCalculate: function (data) {
      var self = this;

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
        self._setCalculateResponse(res);
        $(self.classes.calculator).find(self.classes.loader).hide();
        $(self.classes.tab).removeClass('d-none');
        $(self.classes.calculator).removeClass('d-none');
      });
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
        var result = $('#hellobank')
          .find('.tabs__content.active')
          .find('.catalog-calculator');
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
     * Bundle check
     */
    _bundleCheck: function () {
      var self = this;

      $('.product-info-price [data-role=priceBox]').on('updatePrice', function () {
        var data = $('.tabs__content');
        var priceBox = $('.product-info-price [data-role=priceBox]').data('mage-priceBox').cache.displayPrices;
        var finalPrice;

        if (!$.isEmptyObject(priceBox)) {
          if (priceBox.finalPrice.final != null) {
            finalPrice = Math.round(priceBox.finalPrice.final);
          } else {
            finalPrice = Math.round(priceBox.finalPrice.amount);
          }
        }

        $(self.classes.calculator).find('.form__value > .price').text(finalPrice);

        $(data).each(function () {
          var barem = $(this);
          var id = barem.attr('data-barem-id');
          var min = barem.attr('data-barem-min');
          var max = barem.attr('data-barem-max');

          if (min <= finalPrice && max >= finalPrice) {
            barem.removeClass('d-none');
            $('.tabs__title[data-barem-id="' + id + '"]').removeClass('d-none');
          }
          else {
            barem.addClass('d-none');
            $('.tabs__title[data-barem-id="' + id + '"]').addClass('d-none');
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

        self._actionCalculator(finalPrice);

        $(self.classes.calculator)
          .find('.tabs__title.active')
          .trigger('click');
      });
    },

    /**
     * Update config
     */
    _updateConfig: function () {
      var self = this;
      $('.product-info-price [data-role=priceBox]').on('updatePrice', function () {
        var productId = $('[data-role="swatch-options"]').data('mageOanderSwatchRenderer').getProduct();
        var priceBox = $('.product-info-price [data-role=priceBox]').data('mage-priceBox').cache.displayPrices;
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
      var tabs = $('.tabs--calculator');
      var titles = tabs.find('.tabs__titles');
      var contents = tabs.find('.tabs__contents');
      var finalPrice = price;

      var sortedData = data.sort(function (a, b) {
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
        $(self.classes.tab).removeClass('d-none');
        $(self.classes.calculator).removeClass('d-none');
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

      if (data.min_price <= price && data.max_price >= price) {
        titles.append(
          $('<div/>')
            .addClass('tabs__title' + (key == 0 ? ' active' : ''))
            .attr('data-tab-index', key)
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

      if (key == 0) content.addClass('active');
      content.attr('data-tab-index', key);
      content.attr('data-barem-id', data.barem_id);
      content.find('.form__value.amount > .price').html(this._getFormattedPrice(price));
      content.find('.form__installment.months').html(data.default_installment);

      if (data.equity == "0") {
        content.find('.form__label.down-payment').hide();
        content.find('.col-dp').hide();
        content.find('.col-dp-btn').removeClass('col-sm-6').addClass('col-sm-12');
      }
      else if (typeof data.equity === 'number' && data.equity > 0) {
        content.find('.col-dp-btn').find('[name="down-payment"]').val(data.equity);
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

      if (data.min_price <= price && data.max_price >= price) {
        contents.append(content.removeClass('d-none'));
      }
    }
  });

  return $.oander.oanderHelloBank;
});
