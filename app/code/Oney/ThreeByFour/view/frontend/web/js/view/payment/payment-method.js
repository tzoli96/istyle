define(
    [
        'jquery',
        'ko',
        'mage/translate',
        'Magento_Checkout/js/view/payment/default',
        'Oney_ThreeByFour/js/model/config',
        'Magento_Checkout/js/model/quote',
        'mage/url'
    ],
    function ($, ko, t, Component, config, quote, url) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Oney_ThreeByFour/payments/method',
                validate_url: 'facilypay/validate/phone',
                error: ko.observable(null),
                verifiedBilling: ko.observable(false),
            },
            getBillingAddressFormName: function () {
                return "billing-address-form-oney_facilypay";
            },
            verifyAddress: function (address) {
                if (address == null) {
                    return false;
                }

                const phone_validated = config.getPhoneRegex().test(address.telephone)

                if (!phone_validated) {
                    this.error(config.getError('phone'));
                }

                return phone_validated;
            },
            verified: function () {
                return this.verifiedBilling();
            },
            initialize: function () {
                var self = this;
                this._super();
                this.verifiedBilling(this.verifyAddress(quote.billingAddress()));

                this.renderSimulationText(quote.getTotals()());

                quote.getTotals().subscribe(function (value) {
                    self.renderSimulationText(value);
                });

                quote.billingAddress.subscribe(function (address) {
                    self.verifiedBilling(self.verifyAddress(address));
                })
                return true;
            },
            afterPlaceOrder: function () {
                window.location.replace(window.checkoutConfig.payment.oney_facilypay.redirect_url);
                sleep(5);
            },
            getTitle: function () {
                return config.getTitle(this.getCode());
            },
            getNumber: function () {
                return config.getNumber(this.getCode());
            },
            getContentId: function () {
                return this.getCode() + "_content";
            },
            getBulleId: function () {
                return this.getCode() + "_bulle";
            },
            renderSimulationText: function (value) {
                var self = this;

                $.get(
                    url.build('facilypay/payment/simulation') + "?amount=" + value['base_grand_total'] + "&code=" + this.getCode()
                ).done(function (res) {
                    res = JSON.parse(res);
                    $("#" + self.getBulleId())[0].innerHTML = res.instalments.length + 1;
                    $("#" + self.getContentId())[0].innerHTML = config.translate("Simulation Text")
                        .replace("%1", config.formatPriceForQuote(res.down_payment_amount, quote))
                        .replace("%2", res.instalments.length)
                        .replace("%3", config.formatPriceForQuote(res.instalments[0].instalment_amount, quote))
                        .replaceAll("%4", config.formatPriceForQuote(res.payment_amount + res.total_cost, quote))
                        .replace("%5", config.formatPriceForQuote(res.total_cost, quote))
                        .replace("%6", res.effective_annual_percentage_rate.toFixed(1).toString().replace('.', ","))
                        .replace("%7", res.nominal_annual_percentage_rate.toFixed(2).toString().replace('.', ","))
                })
            },
            getLegalText(){
                return config.translate('Legal Text');
            },
            modalPedagogic: function () {
                $(".legal-block").on('click', function () {
                    $(".modal-inner-wrap").css("width","60%")
                    $("#oney_pedagogique_modal").modal("openModal").on('modalclosed',function () {
                        $(".modal-inner-wrap").css("width","auto")
                    });
                });
            }
        });
    }
);
