define(
    [
        'Oander_ExternalRoundingUnit/js/view/checkout/summary/external_rounding'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            /**
             * Use to define amount is display setting.
             *
             * @override
             */
            isDisplayed: function () {
                return window.checkoutConfig.external_rounding_enabled;
            }
        });
    }
);