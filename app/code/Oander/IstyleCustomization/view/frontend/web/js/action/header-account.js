require([
    'jquery',
    'Magento_Customer/js/customer-data',
    'domReady!'
], function ($, customerData) {
    'use strict';
    const options = {
        url: '/istylecustomization/customer/header',
        headerId: '#header-account-toggle',
        iconClass: ".icon-user",
        monogramClass: ".monogram-user"
    };
    var customer = customerData.get('customer');
    customer.subscribe(function (updatedCustomer)
    {
        if(updatedCustomer.monogram !== undefined) {
            if (updatedCustomer && $(options.headerId + " " + options.iconClass).is(":visible")) {
                $(options.headerId + " " + options.iconClass).hide();
                if ($(options.headerId + " " + options.monogramClass).length) {
                    $(options.headerId + " " + options.monogramClass).show();
                } else {
                    $(options.headerId).html("<span class='" + options.monogramClass + "'>" + updatedCustomer.monogram + "</span>");
                }

            } else {
                $(options.headerId + " " + options.monogramClass).hide();
                $(options.headerId + " " + options.iconClass).show();
            }
        }
    }, this);
});