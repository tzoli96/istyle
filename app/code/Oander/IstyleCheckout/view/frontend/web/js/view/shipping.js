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
	'mage/translate',
	'Oander_IstyleCheckout/js/view/billing-address/sort',
	'domReady!'
], function ($, ko, customer, quote, checkoutData, helpers, L, getPaymentInformationAction, store, saveShipping, $t, sort) {
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
		shippingMethodContinueBtn: store.shippingMethod.continueBtn,

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
			return quote.shippingMethod() ? quote.shippingMethod().method_title : $t('Please select shipping method.');
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

		checkSelectedShippingMethod: function (method) {
			if (helpers.shippingMethodVisibleHandling(method)) {
				var stores = window.checkoutConfig.istyle_checkout.stores;
				var selectedShippingMethod = method.split('_');
				var selected = stores[selectedShippingMethod[selectedShippingMethod.length - 1]];

				if (selected.regionId && (typeof selected.regionId != 'number')) {
					store.shippingMethod.continueBtn(false);
				}
			}
		},

		checkStepContent: function () {
			var self = this;
			var currentLS = store.getLocalStorage();
			var firstShippingMethod = ko.observable('');
			var firstShippingMethodLoaded = false;

			quote.shippingMethod.subscribe(function (value) {
				if (value) {
					if (!firstShippingMethodLoaded) {
						firstShippingMethod(value.method_code);
						firstShippingMethodLoaded = true;
					}

					this.shippingMethodTab(value.method_code);
					store.shippingMethod.continueBtn(true);

					this.checkSelectedShippingMethod(value.method_code);
				}
				else {
					store.shippingMethod.continueBtn(false);
				}
			}, this);

			if (currentLS.shippingMethod) {
				if (currentLS.shippingMethod.selectedCode) {
					this.shippingMethodTab(currentLS.shippingMethod.selectedCode);
					store.shippingMethod.continueBtn(true);

					$('.shipping-control-row[data-code="' + currentLS.shippingMethod.selectedCode + '"]').trigger('click');
				}
				else {
					store.shippingMethod.continueBtn(false);
				}
			}

			// Shipping method
			if (store.steps.shippingMethod() || currentLS.steps.shippingMethod) {
				this.isShippingMethodVisible(true);
				if (store.steps.visible.indexOf('shippingMethod') < 0) {
					store.steps.visible.push('shippingMethod');
				}
			}

			store.steps.shippingMethod.subscribe(function (value) {
				if (value === true) {
					this.isShippingMethodVisible(true);
					if (store.steps.visible.indexOf('shippingMethod') < 0) {
						store.steps.visible.push('shippingMethod');
					}
				}
			}, this);

			if (store.steps.active() === 'shippingMethod'
				|| currentLS.steps.active === 'shippingMethod') {
				this.isShippingMethodVisible(true);
				if (store.steps.visible.indexOf('shippingMethod') < 0) {
					store.steps.visible.push('shippingMethod');
				}
			}

			store.steps.active.subscribe(function (value) {
				if (value === 'shippingMethod') {
					this.isShippingMethodVisible(true);
					if (store.steps.visible.indexOf('shippingMethod') < 0) {
						store.steps.visible.push('shippingMethod');
					}
				}
			}, this);

			var triggerWasSuccess = false;

			// Shipping method
			quote.shippingMethod.subscribe(function (value) {
				var currentLS = store.getLocalStorage();

				if (self.isReservationCheckout()) {
					if (currentLS.shippingMethod) {
						if (currentLS.shippingMethod.selectedCode != value.method_code) {
							if (helpers.shippingMethodVisibleHandling(firstShippingMethod())) {
								if (!helpers.shippingMethodVisibleHandling(value.method_code)) {
									checkoutData.setShippingAddressFromData(null);
									self.resetForm();
								}
								else {
									if (!helpers.shippingMethodVisibleHandling(value.method_code)) {
										if (!triggerWasSuccess) {
											triggerWasSuccess = true;
											self.shippingBtnTrigger();
										}
									}
								}
							}
							else {
								if (!helpers.shippingMethodVisibleHandling(value.method_code)) {
									if (!triggerWasSuccess) {
										triggerWasSuccess = true;
										self.shippingBtnTrigger();
									}
								}
							}
						}
					}
				}

				store.shippingMethod.selectedTitle(value.method_title);
				store.shippingMethod.selectedCode(value.method_code);
				store.shippingMethod.selectedCarrierCode(value.carrier_code);

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

		resetForm: function () {
			var formInterval = setInterval(function () {
				if ($('#co-shipping-form')
					.find('[name="shippingAddress.lastname"]')
					.find('.form-control').length) {
					var requiredFields = ['firstname', 'lastname', 'postcode', 'city', 'street.0', 'telephone'];
					var form = $('#co-shipping-form');

					for (var field in requiredFields) {
						var fieldItem = requiredFields[field];

						form
							.find('[name="shippingAddress.' + fieldItem + '"]')
							.find('.form-control')
							.val('')
							.trigger('change');

						form
							.find('[name="shippingAddress.' + fieldItem + '"]')
							.removeClass('_error');

						form
							.find('[name="shippingAddress.' + fieldItem + '"]')
							.find('.mage-error')
							.remove();
					}

					form.trigger('reset');

					clearInterval(formInterval);
				}
			}, 500);
		},

		shippingBtnTrigger: function () {
			var shippingBtnInterval = setInterval(function () {
				if ($('#co-shipping-form').length) {
					if ($('#co-shipping-form').find('[name="shippingAddress.lastname"]').find('.form-control').length) {
						if ($('#co-shipping-form').find('[name="shippingAddress.lastname"]').find('.form-control').val().length > 0) {
							$('.block--shipping-address').find('[data-role="opc-continue"]').trigger('click');

							clearInterval(shippingBtnInterval);
						}
					}
				}
			}, 500);
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
				if (store.steps.visible.indexOf('shippingAddress') < 0) {
					store.steps.visible.push('shippingAddress');
				}
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
						sort.streetFieldHandler(document.querySelector('.form-shipping-address .form-group.street'));
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
				secondArray = [],
				days = [
					$t('Monday'),
					$t('Tuesday'),
					$t('Wednesday'),
					$t('Thursday'),
					$t('Friday'),
					$t('Saturday'),
					$t('Sunday'),
				];

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

		/**
		 * Check if precheckout
		 * @returns {Boolean}
		 */
		isPrecheckout: function () {
			return window.location.href.indexOf('/precheckout/') > -1 ? true : false;
		},

		/**
		 * Check if reservation precheckout
		 * @returns {Boolean}
		 */
		isReservationCheckout: function () {
			return window.location.href.indexOf('/productreservation/checkout/') > -1 ? true : false;
		},

		/**
		 * Check if card edit should be visible
		 * @returns {Boolean}
		 */
		isCardEditVisible: function (param) {
			return ko.computed(function () {
				var currentLS = store.getLocalStorage(),
					activeStep,
					visibleSteps,
					visible = ko.observable(true);

				if (store.steps.active() !== '') {
					activeStep = store.steps.active()
				} else {
					if (currentLS && currentLS.hasOwnProperty('steps') && currentLS.steps.hasOwnProperty('active')) {
						activeStep = currentLS.steps.active;
					} else {
						activeStep = 'auth';
					}
				}

				if (currentLS && currentLS.hasOwnProperty('steps') && currentLS.steps.hasOwnProperty('visible')) {
					visibleSteps = currentLS.steps.visible;
				} else {
					visibleSteps = ['auth'];
				}

				if (visibleSteps.indexOf(param) > -1) {
					if (store.steps.order.indexOf(activeStep) < store.steps.order.indexOf(param)) {
						visible(false);
					}
				}

				return visible();
			});
		},

		expressMessageWarning: ko.computed(function () {
			var currentLS = store.getLocalStorage();

			if (window.checkoutConfig.expressShippingConfig
				&& window.checkoutConfig.expressShippingConfig.postcode_warning_msg) {
				if (store.shippingMethod) {
					if (store.shippingMethod.expressShippingIsValid()) {
						return window.checkoutConfig.expressShippingConfig.postcode_warning_msg;
					}
					else {
						return '';
					}
				}

				if (currentLS.hasOwnProperty('shippingMethod')) {
					if (currentLS.shippingMethod.hasOwnProperty('expressShippingIsValid')) {
						if (currentLS.shippingMethod.expressShippingIsValid) {
							return window.checkoutConfig.expressShippingConfig.postcode_warning_msg;
						}
						else {
							return '';
						}
					}
				}
			}
		}),

		expressMessageHandler: ko.computed(function () {
			var currentLS = store.getLocalStorage();

			if (quote.shippingAddress()
				&& quote.shippingAddress().postcode
				&& quote.shippingAddress().postcode !== null) {
				if (store.shippingMethod) {
					if (store.shippingMethod.expressShippingIsValid()) {
						return helpers.checkPostcodeExpressShipping(quote.shippingAddress().postcode);
					}
					else {
						return false;
					}
				}

				if (currentLS.hasOwnProperty('shippingMethod')) {
					if (currentLS.shippingMethod.hasOwnProperty('expressShippingIsValid')) {
						if (currentLS.shippingMethod.expressShippingIsValid) {
							return helpers.checkPostcodeExpressShipping(quote.shippingAddress().postcode);
						}
						else {
							return false;
						}
					}
				}
			}
		})
	};

	return function (target) {
		return target.extend(mixin);
	};
});
