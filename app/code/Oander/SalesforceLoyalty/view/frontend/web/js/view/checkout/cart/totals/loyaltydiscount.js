define(
    [
        'Oander_SalesforceLoyalty/js/view/checkout/summary/loyaltydiscount'
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
                return true;
            }
        });
    }
);