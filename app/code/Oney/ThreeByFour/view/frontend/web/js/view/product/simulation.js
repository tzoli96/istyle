define(
    [
        'jquery',
        'uiComponent',
        'ko',
        'mage/url',
        'Magento_Catalog/js/price-utils'
    ],
    function ($, Component, ko, url, priceUtils) {
        "use strict";
        return Component.extend({
            default: {
                form: "#product_addtocart_form",
                amount: {},
                amountType: "finalPrice",
                options: {}
            },
            initialize: function () {
                this._super();
                var self = this;
                $(".super-attribute-select").on('change', this._onOptionChange.bind(this));
                $('[data-price-type]', $("[data-role='priceBox']")).each(function (id, element) {
                    self.default.amount[element.dataset.priceType] = parseInt(element.dataset.priceAmount);
                });
            },
            _onOptionChange: function onOptionChange(event) {
                var tmp_array = [];
                this.default.options[$(event.target).attr('name')] = $(event.target).val();
                for (var value in this.default.options) {
                    tmp_array.push(parseInt(this.default.options[value]));
                }
                tmp_array = tmp_array.sort((a, b) => {
                    return (a < b) ? -1 : 1;
                });

                $('.simulations-content').hide();
                if ($(".simulation-"+tmp_array.join('-')).length > 0) {
                    $(".simulation-"+tmp_array.join('-')).show()
                    $(".payin-oney").show()
                } else {
                    $(".payin-oney").hide()
                    $("#container_oney_simulation").hide()

                }
            },
            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price.toFixed(2));
            },
            updateSimulations: function (total) {
                var self = this;
                $.get(
                    url.build('facilypay/payment/simulation') + "?amount=" + total
                ).done(function (res) {
                    res = JSON.parse(res);
                    res.forEach(function (simulation) {
                        var code = simulation.business_transaction_details[0].business_transaction_code;
                        if ($("#" + code).length > 0) {
                            $("#" + code)[0].innerText = self.getFormattedPrice(simulation.down_payment_amount);
                            $("#" + code + "_instalment")[0].innerText = self.getFormattedPrice(simulation.instalments[0].instalment_amount);
                            $("#" + code + "_total")[0].innerText = self.getFormattedPrice(simulation.payment_amount + simulation.total_cost);
                            $("#" + code + "_comm")[0].innerText = self.getFormattedPrice(simulation.total_cost);
                            $("#" + code + "_taeg")[0].innerText = simulation.effective_annual_percentage_rate + " %";
                            if ($("#" + code + "_tin").length > 0) {
                                $("#" + code + "_tin")[0].innerText = simulation.nominal_annual_percentage_rate + " %";
                            }
                            if ($("#" + code + "_mtic").length > 0) {
                                $("#" + code + "_mtic")[0].innerText = self.getFormattedPrice(simulation.payment_amount + simulation.total_cost);
                            }
                        }
                    })
                })
            }
        })
    }
);
