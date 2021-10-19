<?php

/**
 *  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2018 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */




/**
 *
 * @author nicoeigenmann
 * @Bean
 */
class Customweb_FirstDataConnect_BackendOperation_Adapter_RefundAdapter extends Customweb_FirstDataConnect_AbstractAdapter implements 
		Customweb_Payment_BackendOperation_Adapter_Service_IRefund {

	public function refund(Customweb_Payment_Authorization_ITransaction $transaction){
		$items = $transaction->getTransactionContext()->getOrderContext()->getInvoiceItems();
		
		return $this->partialRefund($transaction, $items, true);
	}

	public function partialRefund(Customweb_Payment_Authorization_ITransaction $transaction, $items, $close){
		$transaction->refundByLineItemsDry($items, $close);
		$amount = Customweb_Util_Invoice::getTotalAmountIncludingTax($items);
		
		$currency = $transaction->getCurrencyCode();
		$formattedAmount = Customweb_Util_Currency::formatAmount($amount, $currency);
		
		$ipgTransactionDetails = new Customweb_FirstDataConnect_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails();
		$ipgTransactionDetails->setOrderId(
				Customweb_FirstDataConnect_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max::_()->set($transaction->getPaymentId()));

		//Create XML
		$tDate = $transaction->getCaptureTDate();
		if($tDate != null) {
			$ipgTransactionDetails->setTDate(Customweb_FirstDataConnect_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TDate::_()->set($tDate));
		}
		
		$ipgPayment = new Customweb_FirstDataConnect_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_Payment();
		$ipgPayment->setChargeTotal(Customweb_FirstDataConnect_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_AmountValueType::_()->set($formattedAmount))->setCurrency(
				Customweb_FirstDataConnect_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_CurrencyType::_()->set($currency));
		
		$ipgTransaction = new Customweb_FirstDataConnect_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionElement();
		
		$paymentMethod = $this->getPaymentMethodFactory()->getPaymentMethod($transaction->getPaymentMethod(), $transaction->getAuthorizationMethod());
		Customweb_FirstDataConnect_Util::setTxTypeForPaymentMethod($paymentMethod, Customweb_FirstDataConnect_IConstants::OPERATION_REFUND, 
				$ipgTransaction);
		
		$basket = $paymentMethod->getBasketForBackend($items, $transaction);
		if($basket !==null) {
			$ipgTransaction->setBasket($basket);
		}
				
		$ipgTransaction->setTransactionDetails($ipgTransactionDetails);
		$ipgTransaction->setPayment($ipgPayment);
		
		
		$orderRequest = new Customweb_FirstDataConnect_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_IPGApiOrderRequest();
		$orderRequest->setTransaction($ipgTransaction);
		
		$isMoto = $transaction->isMoto();
		$service = new Customweb_FirstDataConnect_SoapService($this->getAPIUser($isMoto), $this->getAPIPassword($isMoto), $this->getAPIUrl());
		$service->setClientCertificate(Customweb_FirstDataConnect_Util::getCertificate($this->getConfiguration(), $transaction->isMoto()));
		$service->setClientCertificatePassphrase($this->getCertificatePassphrase($isMoto));
		
		//Send XML handle
		$errorMessage = null;
		try {
			$response = $service->iPGApiOrder($orderRequest);
		}
		catch (Customweb_Soap_Exception_SoapFaultException $e) {
			
			if ($e->getFaultCode() == 'SOAP-ENV:Server') {
				$errorMessage = 'Server Error! Please contact FirstData Connect';
			}
			elseif ($e->getFaultCode() == 'SOAP-ENV:Client') {
				$errorMessage = 'Unknown Error';
				$fault =  $e->getFaultDetail();
				
				if(isset($fault->nodeValue)) {
					$errorMessage = (string) $fault->nodeValue;
				}
				
			}
			else {
				$errorMessage = 'Unknown Error';
			}
		}
		if (empty($errorMessage) && ($response->getErrorMessage() != null && $response->getErrorMessage()->__toString() != "")) {
			$errorMessage = $response->getErrorMessage()->__toString();
		}
		if (empty($errorMessage)) {
			$approvalCode = $response->getApprovalCode()->__toString();
			if (substr($approvalCode, 0, 1) != 'Y') {
				$errorMessage = $approvalCode;
			}
		}
		if (empty($errorMessage)) {
			$transaction->refundByLineItems($items, $close);
		}
		else {
			throw new Exception($errorMessage);
		}
	}
}