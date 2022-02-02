require([
    'jquery'
], function ($) {
    $(window).on("load", function () {
        require([
            'Magento_Customer/js/customer-data',
        ], function (customerData) {
            const options = {
                url: '/istylecustomization/customer/header',
                headerId: '#header-account-toggle',
                sidebarId: '#profile-user-profile-monogram',
                iconClass: ".icon-user",
                monogramClass: "monogram-user"
            };

            var customer = customerData.get('customer');

            if (!customer() || !customer().length) {
                customerData.invalidate(['customer']);
                customerData.reload(['customer'], true);
            }
   
            customer.subscribe(function (updatedCustomer) {
                if (updatedCustomer.monogram !== undefined) {
                    if (updatedCustomer && $(options.headerId + " " + options.iconClass).is(":visible")) {
                        $(options.headerId + " " + options.iconClass).hide();
                        if ($(options.headerId + " " + options.monogramClass).length) {
                            $(options.headerId + " " + options.monogramClass).show();
                        } else {
                            $(options.headerId).html("<span class='" + options.monogramClass + "'>" + updatedCustomer.monogram + "</span>");
                            $(options.sidebarId).html("<span class='" + options.monogramClass + "'>" + updatedCustomer.monogram + "</span>");
                        }
                    } else {
                        $(options.headerId + " " + options.monogramClass).hide();
                        $(options.headerId + " " + options.iconClass).show();
                    }
                }
            }, this);
        });
    });
});
