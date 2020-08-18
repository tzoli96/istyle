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
                window.location.replace(window.checkoutConfig.payment[this.item.method].redirectUrl + '?downpmnt=' + this.getDownPmnt());
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

            getActiveBarems: function () {
                var activeBarem = this.getCofidisData().barem,
                    barems = this.baremData()[activeBarem],
                    baremHtml = "";

                for (const [key, value] of Object.entries(barems)) {
                    if (key == 10) {
                        baremHtml += '<span data-title="' + key + '" data-intervalmin="' + value['intervalMin'] + '" data-intervalmax="' + value['intervalMax'] + '" data-intervalthm="' + value['intervalThm'] + '" data-intervalrate="' + value['intervalRate'] + '" class="active"></span>';
                    }
                    else {
                        baremHtml += '<span data-title="' + key + '" data-intervalmin="' + value['intervalMin'] + '" data-intervalmax="' + value['intervalMax'] + '" data-intervalthm="' + value['intervalThm'] + '" data-intervalrate="' + value['intervalRate'] + '"></span>';
                    }
                }

                return baremHtml;
            },

            getActiveBaremsLength: function () {
                var baremsLength = 0;

                for (const [key, value] of Object.entries(this.baremData()[this.getCofidisData().barem])) {
                    baremsLength++;
                }

                baremsLength = baremsLength - 1;

                return parseInt(baremsLength);
            },

            calculatorRange: function () {
                var step = $(selectors.calculator).find(selectors.calculatorRange).val(),
                    calculatorStep = $(selectors.calculator).find(selectors.calculatorSteps);

                calculatorStep.removeClass('active');
                $(calculatorStep[step]).addClass('active');

                this.getAjaxRequest();
            },

            getMinDownPmnt: function () {
                var minDownPmnt = parseInt(this.getCofidisData().amount * 0.2);

                return minDownPmnt;
            },

            downPmnt: function () {
                downPmnt = $(selectors.downPmnt).val();
            },

            getDownPmnt: function () {
                return $(selectors.downPmnt).val();
            },

            getAjaxRequest: function () {
                $('.calculator-loader').addClass('show');

                var cofidisData = this.getCofidisData(),
                    ajaxUrl = window.location.protocol + '//' + window.location.hostname + '/cofidis/product/index',
                    shopId = cofidisData.shopId,
                    barem = cofidisData.barem,
                    amount = cofidisData.amount,
                    month = $(selectors.calculator).find('span.active').attr('data-title'),
                    downpmnt = downPmnt,
                    thisData = this,
                    minDownpmnt = parseInt(amount * 0.2),
                    hideSteps = 0,
                    stepsCount = 0;

                $(selectors.calculator).find('span').each(function () {
                    var dataIntervalMin = $(this).attr('data-intervalmin'),
                        dataIntervalMax = $(this).attr('data-intervalmax'),
                        dataMonth = $(this).attr('data-title');

                    $(this).removeClass('hide');
                    stepsCount++;

                    if (cofidisData.amount >= parseInt(dataIntervalMin) && cofidisData.amount <= parseInt(dataIntervalMax)) {
                        console.log('Month available: ', dataMonth, cofidisData.amount, parseInt(dataIntervalMin), parseInt(dataIntervalMax));
                    } else {
                        $(this).addClass('hide');
                        hideSteps++;
                    }
                });

                $(selectors.calculatorRange).attr('max', (stepsCount - hideSteps) - 1);

                $('.calculator-result').removeClass('has-error');
                $('.calculator-result').removeClass('error-response');

                if (downpmnt >= minDownpmnt) {
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
                        dataType: 'json'
                    }).done(function (data) {
                        if (data.CalcData) {
                            thisData.setAjaxResponse(data.CalcData[0].Amount, data.CalcData[0].Month, data.CalcData[0].THM, data.CalcData[0].Installment);
                        }
                        else if (data.Error) {
                            $('.calculator-result').addClass('has-error error-response');
                        }

                        $('.calculator-loader').removeClass('show');
                    });
                }
                else {
                    $('.calculator-result').addClass('has-error');
                    $('.calculator-result').find('.price').html('&nbsp;' + thisData.getFormatPrice(minDownpmnt) + ' Ft');

                    $('.calculator-loader').removeClass('show');
                }
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
