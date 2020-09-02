define(
    [
        'jquery',
        'mage/translate',
        'Magento_Checkout/js/view/payment/default'
    ],
    function ($, $t, Component) {
        'use strict';

        var selectors = {
            currentTab: '.tabs__content.active',
            calculator: '.calculator-range-wrapper',
            calculatorSteps: 'span',
            calculatorRange: '.slider',
            actionCalculator: '.action-calculator',
            downPmnt: 'input[name="down-payment"]',
        },
            downPmnt = $(selectors.downPmnt).val();

        var responses = {
            amount: '.form__value .price',
            totalPayable: '.result__value.total-payable .price',
            installmentMonths: '.form__installment.months',
            downPmnt: '.form-control[name="down-payment"]',
            monthlyInstalment: '.result__value.monthly-instalment .price',
            thm: '.result__value.thm .value',
        }

        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Oander_CofidisPayment/payment/cofidis'
            },

            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },

            afterPlaceOrder: function () {
                window.location.replace(window.checkoutConfig.payment[this.item.method].redirectUrl + '?downpmnt=' + this.getDownPmnt() + '&barem=' + this.getActiveBarem());
            },

            getInstructions: function () {
                return window.checkoutConfig.payment[this.item.method].instructions;
            },

            getCofidisData: function () {
                return window.checkoutConfig.payment[this.item.method].data;
            },

            baremData: function () {
                return window.checkoutConfig.payment[this.item.method].barems;
            },

            calculatorRange: function () {
                var activeRange = $(selectors.currentTab).find(selectors.calculator).find(selectors.calculatorRange).val(),
                    steps = $(selectors.currentTab).find(selectors.calculator).find(selectors.calculatorSteps);

                steps.removeClass('active');
                $(steps[activeRange]).addClass('active');

                $(selectors.currentTab).find('.action-calculator').trigger('click');
            },

            getActiveBarem: function () {
                return $(selectors.currentTab).attr('data-grp');
            },

            getMaxRange: function (inst) {
                return inst.length - 1;
            },

            downPmnt: function () {
                downPmnt = $(selectors.downPmnt).val();
            },

            getDownPmnt: function () {
                return $(selectors.currentTab).find(selectors.downPmnt).val();
            },

            getMinDownPmnt: function (min, max, limit, perc) {
                var cartAmount = parseInt(this.getCofidisData().amount),
                    newDownPmnt = 0;

                if (cartAmount > parseInt(max)) {
                    newDownPmnt = cartAmount - parseInt(max);
                }
                else if (cartAmount > parseInt(limit)) {
                    newDownPmnt = cartAmount * (parseInt(perc) / 100);
                }

                return newDownPmnt;
            },

            getAjaxRequest: function () {
                $(selectors.currentTab).find(responses.downPmnt).trigger('keyup');

                var cofidisData = this.getCofidisData(),
                    ajaxUrl = window.location.protocol + '//' + window.location.hostname + '/cofidis/product/index',
                    shopId = cofidisData.shopId,
                    barem = parseInt($(selectors.currentTab).attr('data-grp')),
                    amount = parseInt(cofidisData.amount),
                    month = parseInt($(selectors.currentTab).find(selectors.calculator).find('span.active').attr('data-title')),
                    downpmnt = parseInt($(selectors.currentTab).find(responses.downPmnt).val()),
                    minDownpmnt = parseInt($(selectors.currentTab).attr('data-min')),
                    maxDownpmnt = parseInt($(selectors.currentTab).attr('data-max')),
                    baremLimit = parseInt($(selectors.currentTab).attr('data-limit')),
                    baremPerc = parseInt($(selectors.currentTab).attr('data-perc')),
                    newDownPmnt = 0,
                    newMaxDownPmnt = parseInt(amount - minDownpmnt),
                    $this = this;

                if (amount > maxDownpmnt) {
                    newDownPmnt = amount - parseInt(maxDownpmnt);
                }
                else if (amount > parseInt(baremLimit)) {
                    newDownPmnt = amount * (parseInt(baremPerc) / 100);
                }

                if (downpmnt > newMaxDownPmnt) {
                    $(selectors.currentTab).find('.messages--cofidis').removeClass('hide');
                    $(selectors.currentTab).find('.messages--cofidis').find('.message.error').html($t('Wrong calculation parameters'));
                }
                else if (downpmnt < newDownPmnt) {
                    $(selectors.currentTab).find('.messages--cofidis').removeClass('hide');
                    $(selectors.currentTab).find('.messages--cofidis').find('.message.error').html(`${$t('Minimum downpayment&nbsp;')} ${this.getFormatPrice(newDownPmnt)} Ft`);
                }
                else {
                    $('.calculator-loader').addClass('show');
                    $(selectors.currentTab).find('.messages--cofidis').addClass('hide');

                    if (xhr && xhr.readyState != null) {
                        xhr.abort();
                    }
                }

                var xhr = $.ajax({
                    url: ajaxUrl,
                    data: {
                        shopId: shopId,
                        barem: barem,
                        amount: amount,
                        downpmnt: downpmnt,
                        month: month,
                    },
                    type: 'GET',
                    dataType: 'json'
                }).done(function (data) {
                    if (data.CalcData) {
                        $this.setAjaxResponse(data.CalcData[0].Amount, data.CalcData[0].Month, data.CalcData[0].THM, data.CalcData[0].Installment);
                    }
                    else if (data.Error) {
                        $(selectors.currentTab).find('.messages--cofidis').removeClass('hide');
                        $(selectors.currentTab).find('.messages--cofidis').find('.message.error').html($t('Wrong calculation parameters'));
                    }

                    $('.calculator-loader').removeClass('show');
                });
            },

            setAjaxResponse: function (amount, month, thm, installment) {
                $(selectors.currentTab).find(responses.amount).html(this.getFormatPrice(amount));
                $(selectors.currentTab).find(responses.totalPayable).html(this.getFormatPrice(installment * month));
                $(selectors.currentTab).find(responses.installmentMonths).html(month);
                $(selectors.currentTab).find(responses.monthlyInstalment).html(this.getFormatPrice(installment));
                $(selectors.currentTab).find(responses.thm).html((thm * 100));
            },

            getFormatPrice: function (x) {
                return x.toLocaleString().replace(',', ' ').split('.')[0];
            },

            tabsClick: function () {
                $(document).on('click', '.tabs__title', function () {
                    var tabIndex = $(this).attr('data-tab-index');

                    $('.tabs__title').removeClass('active');
                    $('.tabs__content').removeClass('active');

                    $(this).addClass('active');
                    $(`.tabs__content[data-tab-index="${tabIndex}"]`).addClass('active');

                    if ($(selectors.currentTab).find(responses.installmentMonths).text() == '') {
                        $(selectors.currentTab).find('.action-calculator').trigger('click');
                    }
                });
            }
        });
    }
);
