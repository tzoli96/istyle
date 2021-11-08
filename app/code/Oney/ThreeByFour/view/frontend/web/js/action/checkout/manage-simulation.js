define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'mage/url',
        'Magento_Catalog/js/price-utils'
    ],
    function ($, quote, url, priceUtils) {
        "use strict";

        function getFormattedPrice(price) {
            return priceUtils.formatPrice(price.toFixed(2), quote.getPriceFormat());
        }

        quote.getTotals().subscribe(function (value) {
            $.get(
                url.build('facilypay/payment/simulation') + "?amount=" + value['base_grand_total']
            ).done(function (res) {
                res = JSON.parse(res);
                res.forEach(function (simulation) {
                    var code = simulation.business_transaction_details[0].business_transaction_code;
                    if ($("#" + code).length > 0) {
                        $("#" + code)[0].innerText = getFormattedPrice(simulation.down_payment_amount);
                        $("#" + code + "_instalment")[0].innerText = getFormattedPrice(simulation.instalments[0].instalment_amount);
                        $("#" + code + "_total")[0].innerText = getFormattedPrice(simulation.payment_amount + simulation.total_cost);
                        $("#" + code + "_comm")[0].innerText = getFormattedPrice(simulation.total_cost);
                        $("#" + code + "_taeg")[0].innerText = simulation.effective_annual_percentage_rate + " %";
                        if ($("#" + code + "_tin").length > 0) {
                            $("#" + code + "_tin")[0].innerText = simulation.nominal_annual_percentage_rate + " %";
                        }
                        if ($("#" + code + "_mtic").length > 0) {
                            $("#" + code + "_mtic")[0].innerText = getFormattedPrice(simulation.payment_amount + simulation.total_cost);
                        }
                    }
                })
            })
        });

        $(".open-simulation").on('click', function () {
            $("#container_oney_simulation").css({'display': 'block'});
        });

        $(".close-simulation").on('click', function () {
            $("#container_oney_simulation").css({'display': 'none'});
        });
    }
);
