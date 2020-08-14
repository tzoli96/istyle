define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Innobyte_Cetelem/js/action/redirect-on-success'
    ],
    function ($, Component) {
        'use strict';

        var selectors = {
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
                window.location.replace(window.checkoutConfig.payment[this.item.method].redirectUrl);
            },

            getInstructions: function () {
                return window.checkoutConfig.payment[this.item.method].instructions;
            },

            getCofidisIframeSource: function () {
                return "https://cofidis.webdesign.hu/webkalk/?" + window.checkoutConfig.payment[this.item.method].params;
            },

            getCofidisData: function () {
                return window.checkoutConfig.payment[this.item.method].data;
            },

            calculatorRange: function () {
                var step = $(selectors.calculator).find(selectors.calculatorRange).val(),
                    calculatorStep = $(selectors.calculator).find(selectors.calculatorSteps);

                calculatorStep.removeClass('active');
                $(calculatorStep[step]).addClass('active');

                this.getAjaxRequest();
            },

            downPmnt: function () {
                downPmnt = $(selectors.downPmnt).val();
            },

            getAjaxRequest: function () {
                var cofidisData = this.getCofidisData(),
                    ajaxUrl = window.location.protocol + '//' + window.location.hostname + '/cofidis/product/index',
                    shopId = cofidisData.shopId,
                    barem = cofidisData.barem,
                    amount = cofidisData.amount,
                    month = $(selectors.calculator).find('span.active').attr('data-title'),
                    downpmnt = downPmnt,
                    thisData = this;

                if (xhr && xhr.readyState != null) {
                    xhr.abort();
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
                    dataType: 'json',
                    showLoader: true
                }).done(function (data) {
                    if (data.CalcData) {
                        thisData.setAjaxResponse(data.CalcData[0].Amount, data.CalcData[0].Month, data.CalcData[0].THM, data.CalcData[0].Installment);
                    }
                    else if (data.Error) {
                        console.log(data.Error);
                    }
                });
            },

            setAjaxResponse: function (amount, month, thm, installment) {
                $(responses.amount).html(this.getFormatPrice(amount));
                $(responses.totalPayable).html(this.getFormatPrice(installment * month));
                $(responses.installmentMonths).html(month);
                $(responses.monthlyInstalment).html(this.getFormatPrice(installment));
                $(responses.thm).html((thm * 100));
            },

            getFormatPrice: function (x) {
                return x.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, " ").split('.')[0];
            }
        });
    }
);
