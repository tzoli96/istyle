define([
	'jquery',
	'ko',
	'Magento_Customer/js/model/customer',
	'Magento_Checkout/js/model/quote',
	'Magento_Checkout/js/checkout-data',
	'Oander_IstyleCheckout/js/helpers',
	'Oander_IstyleCheckout/js/leaflet',
	'Magento_Checkout/js/action/get-payment-information',
	'Oander_IstyleCheckout/js/model/store',
	'Magento_Checkout/js/model/shipping-save-processor/default',
	'domReady!'
], function ($, ko, customer, quote, checkoutData, helpers, L, getPaymentInformationAction, store, saveShipping) {
	'use strict';

	// Shipping methods tabs
	$('body').on('click', '.switch--delivery', function (e) {
		e.preventDefault();
		var clickedTab = $(this).attr('href').substring(1);

		$('.delivery-content').addClass('d-none');
		$('.switch--delivery').closest('.data.item.title').removeClass('active')
		$(this).closest('.data.item.title').addClass('active');
		$('#' + clickedTab).removeClass('d-none');

		// Triggering resize because of the open street map centering bug
		window.dispatchEvent(new Event('resize'));
	});

	// Shipping methods onclick
	$('body').on('click', '.shipping-control-row', function () {
		var that = $(this);

		if (that.siblings('.pos').length > 0) {
			// waiting for css transition end
			setTimeout(function () {
				$('html, body').animate({ scrollTop: that.offset().top - 75 }, 500, function () {

					// Triggering resize because of the open street map centering bug
					window.dispatchEvent(new Event('resize'));
				});
			}, 500);
		}
	});

	var mixin = {
		continueBtn: store.shippingAddress.continueBtn,
		isShippingMethodVisible: ko.observable(false),
		isShippingAddressVisible: ko.observable(false),
		shippingMethodContinueBtn: ko.observable(false),

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
			return checkoutData.getSelectedShippingRate() ? (quote.shippingMethod() ? quote.shippingMethod().method_title : '') : 'Please select shipping method.';
		}),

		getShippingAddress: ko.computed(function () {
			return helpers.getShippingAddress();
		}),

		shippingMethodContinue: function () {
			store.steps.shippingMethod(true);

			if (!helpers.shippingMethodVisibleHandling(store.shippingMethod.selectedCode())) {
				store.steps.active('shippingAddress');
				$('.block--shipping-address').find('.card__action').trigger('click');
			}
			else {
				store.steps.active('billingAddress');
				$('.block--billing-address').find('.card__action').trigger('click');

				saveShipping.saveShippingInformation();
			}
		},

		checkStepContent: function () {
			var currentLS = store.getLocalStorage();

			quote.shippingMethod.subscribe(function (value) {
				if (value) {
					this.shippingMethodTab(value.method_code);
					this.shippingMethodContinueBtn(true);
				}
				else {
					this.shippingMethodContinueBtn(false);
				}
			}, this);

			// Shipping method
			if (store.steps.shippingMethod()
			 || currentLS.steps.shippingMethod) {
				 this.isShippingMethodVisible(true);
			}
			store.steps.shippingMethod.subscribe(function (value) {
				if (value === true) {
					this.isShippingMethodVisible(true);
				}
			}, this);

			if (store.steps.active() === 'shippingMethod'
				|| currentLS.steps.active === 'shippingMethod') {
					this.isShippingMethodVisible(true);
			}
			store.steps.active.subscribe(function (value) {
				if (value === 'shippingMethod') {
					this.isShippingMethodVisible(true);
				}
			}, this);

			// Shipping method
			quote.shippingMethod.subscribe(function (value) {
				store.shippingMethod.selectedTitle(value.method_title);
				store.shippingMethod.selectedCode(value.method_code);

				this.shippingAddressVisibleCondition();
			}, this);

			// Shipping address
			if (store.steps.shippingAddress() === true) this.shippingAddressVisibleCondition();

			store.steps.shippingAddress.subscribe(function () {
				this.shippingAddressVisibleCondition();
			}, this);

			if (store.steps.active() === 'shippingAddress') this.shippingAddressVisibleCondition();

			store.steps.active.subscribe(function (value) {
				if (value == 'shippingAddress') this.shippingAddressVisibleCondition();
			}, this);

			this.validateFields();
		},

		/**
		 * Shipping address visible condition
		 * @returns {Void}
		 */
		 shippingAddressVisibleCondition: function () {
			var currentLS = store.getLocalStorage();

			if (helpers.shippingMethodVisibleHandling(store.shippingMethod.selectedCode())) {
				this.isShippingAddressVisible(false);
			}
			else if (!currentLS.steps.shippingMethod && !store.steps.shippingMethod()) {
				this.isShippingAddressVisible(false);
			}
			else {
				this.isShippingAddressVisible(true);
			}
		},

		/**
		 * Shipping method tab
		 * @returns {Void}
		 */
		shippingMethodTab: function (value) {
			if (helpers.shippingMethodVisibleHandling(value)) {
				$('.shipping-methods__tabs .switch--delivery[href="#tab2"]')
					.trigger('click');
			}
		},

		/**
		 * Validate fields
		 * @returns {Void}
		 */
		validateFields: ko.computed(function () {
			if (quote.shippingAddress()) {
				var formInterval = setInterval(function () {
					if ($('.form-shipping-address').length
						&& $('[name="oanderOrderCommentForm.comment"] .form-control').length) {
						helpers.validateShippingFields($('.form-shipping-address'));
						clearInterval(formInterval);
					}
				}, helpers.interval);
			}
		}),

		/**
		 * Is active
		 * @param {String} step
		 * @returns {Boolean}
		 */
		isActive: function (step) {
			var currentLS = store.getLocalStorage();

			if (currentLS.steps && (currentLS.steps.active === step)) {
				if (currentLS.steps.paymentMethod || currentLS.steps.billingAddress) {
					var deferred = $.Deferred();
					getPaymentInformationAction(deferred);
				}

				helpers.stepCounter($('[data-step="' + step + '"]'));
				return true;
			}
			else {
				return false;
			}
		},

		/**
		 * Are tabs needed
		 * @returns {Object}
		 */
		areTabsNeeded: function () {
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
					if (firstItem === 'warehouse_pickup') {
						if (ratesArray[i].carrier_code !== 'warehouse_pickup') {
							areNeeded = true;
							secondArray.push(ratesArray[i]);
						} else {
							firstArray.push(ratesArray[i]);
						}
					} else {
						if (ratesArray[i].carrier_code === 'warehouse_pickup') {
							areNeeded = true;
							secondArray.push(ratesArray[i]);
						} else {
							firstArray.push(ratesArray[i]);
						}
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
		generateMaps: function () {
			var loopThroughArrays = function (array) {
				var maps = [],
						mapIndex = 0;

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
							zoom: 16,
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
						maps[mapIndex].addLayer(layer);

						// Adding marker to the map
						L.marker([latitude, longitude], { icon: icon }).addTo(maps[mapIndex]);

						mapIndex++;
					}
				}
			};

			if (this.areTabsNeeded().firstArray.length > 0 && this.areTabsNeeded().firstArray[0].carrier_code === 'warehouse_pickup') {
				loopThroughArrays(this.areTabsNeeded().firstArray);
			}

			if (this.areTabsNeeded().secondArray.length > 0 && this.areTabsNeeded().secondArray[0].carrier_code === 'warehouse_pickup') {
				loopThroughArrays(this.areTabsNeeded().secondArray);
			}
		},

		// Check if precheckout
		isPrecheckout: function() {
			return window.location.href.indexOf('/precheckout/') > -1 ? true : false;
		},
	};

	return function (target) {
		return target.extend(mixin);
	};
});
