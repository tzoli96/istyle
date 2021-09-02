/**
* Copyright 2019 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

/**
 * Initialization widget to upload html content by Ajax
 *
 * @method ajax(placeholders)
 * @method replacePlaceholder(placeholder, html)
 */
define([
    'jquery',
], function($) {
    "use strict";

    $.widget('mage.salesforceLoyaltyHistory', {
        options: {
            history: [],
        },

        /**
         * Initialize widget
         */
        _create: function () {
            console.log(this.options.history);
        },
    });

    return $.mage.salesforceLoyaltyHistory;
});
