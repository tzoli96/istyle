define([
	'jquery',
	'ko',
	'Magento_Customer/js/model/customer',
	'Magento_Checkout/js/model/quote',
	'Magento_Checkout/js/checkout-data',
	'Oander_IstyleCheckout/js/helpers',
	'Oander_IstyleCheckout/js/leaflet',
	'domReady!'
], function ($, ko, customer, quote, checkoutData, helpers, L) {
	'use strict';

	var pageLoaded = false;

	// @todo: remove settimeout
	var triggerPaymentLoad = function () {
		setTimeout(function () {
		$('.block--checkout-step[data-step="shippingAddress"] .action.next-step').trigger('click');
		}, 2000);
	};

	// Shipping methods tabs
	$('body').on('click', '.switch--delivery', function(e) {
		e.preventDefault();
		var clickedTab = $(this).attr('href').substring(1);
		
		$('.delivery-content').addClass('d-none');
		$('.data.item.title').removeClass('active')
		$(this).closest('.data.item.title').addClass('active');
		$('#' + clickedTab).removeClass('d-none');

		// Triggering resize because of the open street map centering bug
		window.dispatchEvent(new Event('resize'));
	});

	// Shipping methods onclick
	$('body').on('click', '.shipping-control-row', function() {
		var that = $(this);

		if (that.siblings('.pos').length > 0) {
			// waiting for css transition end
			setTimeout(function() {
				$('html, body').animate({ scrollTop: that.offset().top - 75 }, 500, function() {

					// Triggering resize because of the open street map centering bug
					window.dispatchEvent(new Event('resize'));
				});
			}, 500);
		}
	});

	var mixin = {
		/**
		 * Is logged in
		 * @returns {Boolean}
		 */
		isLoggedIn: function () {
			return customer.isLoggedIn() ? false : true;
		},

		/**
		 * Get shipping method
		 * @returns {String}
		 */
		getShippingMethod: ko.computed(function () {
			return checkoutData.getSelectedShippingRate() ? (quote.shippingMethod() ? quote.shippingMethod().method_title : '') : false;
		}),

		getShippingAddress: ko.computed(function () {
			return helpers.getShippingAddress();
		}),

		// @todo: refact: watch quote instead of checkoutdata
		checkStepContent: ko.computed(function () {
			var steps = {
				email: helpers.isFilled((customer.isLoggedIn()) ? window.checkoutConfig.customerData.email : checkoutData.getInputFieldEmailValue()),
				shippingMethod: helpers.isFilled(checkoutData.getSelectedShippingRate()),
				shippingAddress: helpers.isFilled(helpers.getShippingAddress()),
				billingAddress: helpers.isFilled(checkoutData.getBillingAddressFromData()),
				paymentMethod: helpers.isFilled(checkoutData.getSelectedPaymentMethod()),
			};

			var checkShippingMethod = function () {
				if (helpers.isFilled(checkoutData.getSelectedShippingRate())) {
					if (quote.shippingMethod()) return true;
					return false;
				}
				else {
					return true;
				}
			}

			if (!pageLoaded && checkShippingMethod()) {
				pageLoaded = true;

				for (var i in steps) {
					if (steps[i] == '') {
						var findElement = setInterval(function () {
							var element = $('.block--checkout-step[data-step="'+ i +'"]');

							if (element) {
							setTimeout(function () {
								$('.block--checkout-step[data-step="'+ i +'"] .card__action').trigger('click');
							}, 1000);
							clearInterval(findElement);
							}
						}, 500);

						break;
					}
				}
			}
		}),

		isFilled: function (value) {
			return helpers.isFilled(value);
		},

		/**
		 * Are tabs needed
		 * @returns {Object}
		 */
		areTabsNeeded: function() {
			var ratesArray = this.rates(),
				firstItem = '',
				areNeeded = false,
				firstArray = [],
				secondArray = [];

			for (var i = 0; i < ratesArray.length; i++) {
				if (i === 0) {
					firstItem = ratesArray[0].carrier_code;
					firstArray.push(ratesArray[0]);
				} else {
					if (ratesArray[i].carrier_code.charAt(0) !== firstItem.charAt(0)) {
					areNeeded = true;
					secondArray.push(ratesArray[i]);
					} else {
					firstArray.push(ratesArray[i]);
					}
				}
			}

			return {
				needed: areNeeded,
				firstArray: firstArray,
				secondArray: secondArray
			};
		},

		// Genarate OpenStreetMaps
		generateMaps: function() {
			var loopThroughArrays = function(array) {
				var maps = [];

				for (var i = 0; i < array.length; i++) {
					if (array[i].extension_attributes.warehouse_manager_data !== false) {
						var methodCode = array[i].method_code,
								mapElement = document.getElementById('pos-map--' + methodCode),
								template = '<div id="map' + i + '" style="width: 100%; height: 100%;"></div>',
								pinImage = array[i].extension_attributes.warehouse_manager_data[0].pin_image,
								pinWidth = array[i].extension_attributes.warehouse_manager_data[0].pin_width,
								latitude = array[i].extension_attributes.warehouse_manager_data[0].geo_codes.split(',')[0] * 1,
								longitude = array[i].extension_attributes.warehouse_manager_data[0].geo_codes.split(',')[1] * 1;

						mapElement.innerHTML = template;
						
						// Create Leaflet map on map element.
						maps.push(L.map('map' + i, {
							center: [latitude, longitude],
							zoomControl: false,
							zoom: 20,
							dragging: !L.Browser.mobile,
							tap: !L.Browser.mobile
						}));
				
						// Creating marker icon
						var icon = L.icon({
							iconUrl: pinImage,
							iconSize: [pinWidth / 2, 'auto']
						});

						// Creating a Layer object
						var layer = new L.TileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
						
						// Adding layer to the map
						maps[i].addLayer(layer);

						// Adding marker to the map
						L.marker([latitude, longitude], {icon: icon}).addTo(maps[i]);
					}
				}
			};

			if (this.areTabsNeeded().firstArray.length > 0 && this.areTabsNeeded().firstArray[0].carrier_code === 'warehouse_pickup') {
				loopThroughArrays(this.areTabsNeeded().firstArray);
			}

			if (this.areTabsNeeded().secondArray.length > 0 && this.areTabsNeeded().secondArray[0].carrier_code === 'warehouse_pickup') {
				loopThroughArrays(this.areTabsNeeded().secondArray);
			}
		}

	};

	return function (target) {
		return target.extend(mixin);
	};
});
