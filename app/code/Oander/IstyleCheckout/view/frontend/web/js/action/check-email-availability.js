/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 define(
  [
    'mage/storage',
    'Magento_Checkout/js/model/url-builder'
  ],
  function (storage, urlBuilder) {
    'use strict';

    return function (deferred, email) {
      return storage.post(
        urlBuilder.createUrl('/forcelogin/customers/isEmailAvailable', {}),
        JSON.stringify({
          customerEmail: email,
          websiteId: window.checkoutConfig.istyle_checkout.website_id
        }),
        false
      ).done(
        function (res) {
          var res = JSON.parse(res);

          if (res.isEmailAvailable) {
            deferred.resolve();
          } else {
            deferred.reject();
          }
        }
      ).fail(
        function () {
          deferred.reject();
        }
      );
    };
  }
);
