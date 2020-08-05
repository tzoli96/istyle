/**
	* Copyright © 2015 Magento. All rights reserved.
	* See COPYING.txt for license details.
*/
/*browser:true*/
/*global define*/
define(
[
	'Magento_Checkout/js/view/payment/default'
],
function (Component) {
	'use strict';
	
	return Component.extend({
	
		defaults: {
			redirectAfterPlaceOrder: false,
			template: 'Avalon_Costompayment/payment/paymentmethod'
		},
		
		/** Returns send check to info */
		getMailingAddress: function() {
			return window.checkoutConfig.payment.checkmo.mailingAddress;
		},

		getOrderId: function () {
			var _url = "/costompayment/index/tbigetid?tag=jLhrHYsfPQ3Gu9JgJPLJ";

            var param = 'ajax=1';
            jQuery.ajax({
				showLoader: true,
				url: _url,
				data: param,
				type: "POST",
				dataType: 'json'
			}).done(function (data) {
				if(parseInt( data.msg_status ) == 1 ){
					//var retid = data.retid;
					var tbiro_envurl = data.tbiro_envurl;
					//var tbi_pause_txt = data.tbi_pause_txt;
					var tbiro_output64 = data.tbiro_output64;
					//window.location.replace('/tbirocreateid?tag=jLhrHYsfPQ3Gu9JgJPLJ&oid=' + retid + '&tbiro_envurl=' + tbiro_envurl + '&tbi_pause_txt=' + tbi_pause_txt);

					var formData = new FormData();
					formData.append("order_data", tbiro_output64);
					formData.append("providerCode", "avast");
					var xhr = new XMLHttpRequest();
					if ("withCredentials" in xhr) {
						xhr.open('POST', tbiro_envurl, true);
					} else if (typeof XDomainRequest != "undefined") {
						xhr = new XDomainRequest();
						xhr.open('POST', tbiro_envurl);
					}
					xhr.onreadystatechange = function() {
						if (this.readyState == 2) {
							window.location.href = this.responseURL;
						}
					};
					xhr.send(formData);

				}else{
					alert("Error get data!");
				}
			}).fail(function (XMLHttpRequest, textStatus, errorThrown) {
				alert("Error get data!");
			});
        },


		
		afterPlaceOrder: function () {
			this.getOrderId();
		},
	});
}
);
