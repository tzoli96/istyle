/**
 *   /$$$$$$   /$$$$$$  /$$   /$$ /$$$$$$$  /$$$$$$$$ /$$$$$$$
 *  /$$__  $$ /$$__  $$| $$$ | $$| $$__  $$| $$_____/| $$__  $$
 * | $$  \ $$| $$  \ $$| $$$$| $$| $$  \ $$| $$      | $$  \ $$
 * | $$  | $$| $$$$$$$$| $$ $$ $$| $$  | $$| $$$$$   | $$$$$$$/
 * | $$  | $$| $$__  $$| $$  $$$$| $$  | $$| $$__/   | $$__  $$
 * | $$  | $$| $$  | $$| $$\  $$$| $$  | $$| $$      | $$  \ $$
 * |  $$$$$$/| $$  | $$| $$ \  $$| $$$$$$$/| $$$$$$$$| $$  | $$
 *  \______/ |__/  |__/|__/  \__/|_______/ |________/|__/  |__/
 *
 *                            ,-~.
 *                          :  .o \
 *                          `.   _/`.
 *                            `.  `. `.
 *                              `.  ` .`.
 *                                `.  ``.`.
 *                        _._.-. -._`.  `.``.
 *                    _.'            .`.  `. `.
 *                 _.'            )     \   '
 *               .'             _.          "
 *             .'.-.'._     _.-'            "
 *           ;'       _'-.-'              "
 *          ; _._.-.-;  `.,,_;  ,..,,,.:"
 *         %-'      `._.-'   \_/   :;;
 *                           | |
 *                           : :
 *                           | |
 *                           { }
 *                            \|
 *                            ||
 *                            ||
 *                            ||
 *                          _ ;; _
 *                         "-' ` -"
 *
 * Oander_CoreBugFix
 *
 * @author  Gabor Kuti <gabor.kuti@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */
/*global define,alert*/
define(
  [
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'mage/storage',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/select-billing-address',
    'Oander_IstyleCheckout/js/helpers',
    'Oander_IstyleCheckout/js/model/store',
  ],
  function (
    ko,
    quote,
    resourceUrlManager,
    storage,
    paymentService,
    methodConverter,
    errorProcessor,
    fullScreenLoader,
    selectBillingAddressAction,
    helpers,
    store
  ) {
    'use strict';

    return {
      saveShippingInformation: function () {
        var payload, shippingAddress, billingAddress;
        var stores = window.checkoutConfig.istyle_checkout.stores;

        // MODIFICATION: set billing address also if countryId changed
        if (!quote.billingAddress() || quote.billingAddress().countryId !== quote.shippingAddress().countryId) {
          if (!helpers.shippingMethodVisibleHandling(store.shippingMethod.selectedCode())) {
            selectBillingAddressAction(quote.shippingAddress());
          }
        }

        if (helpers.shippingMethodVisibleHandling(store.shippingMethod.selectedCode())) {
          var selectedShippingMethod = store.shippingMethod.selectedCode().split('_');
          var id = selectedShippingMethod[selectedShippingMethod.length - 1];

          shippingAddress = stores[id];

          !quote.billingAddress()
            ? billingAddress = null
            : billingAddress = quote.billingAddress();
        }
        else {
          shippingAddress = quote.shippingAddress();
          billingAddress = quote.billingAddress() ? quote.billingAddress() : quote.shippingAddress();
        }

        if (billingAddress) {
          if (helpers.areAddressesEqual(quote.shippingAddress(), billingAddress)) {
            delete billingAddress.saveInAddressBook;
            delete billingAddress.save_in_address_book;
          }
          else {
            billingAddress.saveInAddressBook = 1;
            billingAddress.save_in_address_book = 1;
          }
        }

        payload = {
          addressInformation: {
            shipping_address: shippingAddress,
            billing_address: billingAddress,
            shipping_method_code: quote.shippingMethod().method_code,
            shipping_carrier_code: quote.shippingMethod().carrier_code
          }
        };

        fullScreenLoader.startLoader();

        return storage.post(
          resourceUrlManager.getUrlForSetShippingInformation(quote),
          JSON.stringify(payload)
        ).done(
          function (response) {
            quote.setTotals(response.totals);
            paymentService.setPaymentMethods(methodConverter(response.payment_methods));
            fullScreenLoader.stopLoader();
          }
        ).fail(
          function (response) {
            errorProcessor.process(response);
            fullScreenLoader.stopLoader();
          }
        );
      }
    };
  }
);
