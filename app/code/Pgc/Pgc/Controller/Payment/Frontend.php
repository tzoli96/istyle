<?php

namespace Pgc\Pgc\Controller\Payment;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class Frontend extends Action
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Session
     */
    private $session;

    /**
     * @var PaymentInformationManagementInterface
     */
    private $paymentInformation;

    /**
     * @var Data
     */
    private $paymentHelper;

    /**
     * @var \Pgc\Pgc\Helper\Data
     */
    private $pgcHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Frontend constructor.
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param Session $checkoutSession
     * @param PaymentInformationManagementInterface $paymentInformation
     * @param Data $paymentHelper
     * @param \Pgc\Pgc\Helper\Data $pgcHelper
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context,
        Session $checkoutSession,
        PaymentInformationManagementInterface $paymentInformation,
        Data $paymentHelper,
        \Pgc\Pgc\Helper\Data $pgcHelper,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->session = $checkoutSession;
        $this->paymentInformation = $paymentInformation;
        $this->paymentHelper = $paymentHelper;
        $this->urlBuilder = $context->getUrl();
        $this->pgcHelper = $pgcHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $request = $this->getRequest()->getPost()->toArray();
        $response = $this->resultJsonFactory->create();

        $paymentMethod = 'pgc_creditcard';

        //TODO: SELECT CORRECT PAYMENT SETTINGS
        \Pgc\Client\Client::setApiUrl($this->pgcHelper->getGeneralConfigData('host'));
        $client = new \Pgc\Client\Client(
            $this->pgcHelper->getGeneralConfigData('username'),
            $this->pgcHelper->getGeneralConfigData('password'),
            $this->pgcHelper->getPaymentConfigData('api_key', $paymentMethod, null),
            $this->pgcHelper->getPaymentConfigData('shared_secret', $paymentMethod, null)
        );
        $transactionType = $this->pgcHelper->getPaymentConfigData('transaction_type', $paymentMethod, null);
        //file_put_contents('/var/www/ikari.aufbix.org/public_html/magento2/sig.txt', print_r($this->pgcHelper->getPaymentConfigData('shared_secret', $paymentMethod, null), TRUE));
        $order = $this->session->getLastRealOrder();
        $transaction = null; 
        switch ($transactionType) {
            case 'debit':
                $transaction = new \Pgc\Client\Transaction\Debit();
                break;
            case 'preauth':
            default:
                $transaction = new \Pgc\Client\Transaction\Preauthorize();
                break;
        }

        /*if ($this->pgcHelper->getPaymentConfigDataFlag('seamless', $paymentMethod)) {
            $token = (string) $request['token'];

            if (empty($token)) {
                die('empty token');
            }

            $transaction->setTransactionToken($token);
        }*/
        //$transaction->addExtraData('3dsecure', 'OPTIONAL');

        $transaction->setTransactionId('magento-' . $order->getIncrementId());
        $transaction->setAmount(\number_format($order->getGrandTotal(), 2, '.', ''));
        $transaction->setCurrency($order->getOrderCurrency()->getCode());

        $customer = new \Pgc\Client\Data\Customer();
        $customer->setFirstName($order->getCustomerFirstname());
        $customer->setLastName($order->getCustomerLastname());
        $customer->setEmail($order->getCustomerEmail());

        $billingAddress = $order->getBillingAddress();
        if ($billingAddress !== null) {
            $customer->setBillingAddress1($billingAddress->getStreet()[0]);
            $customer->setBillingPostcode($billingAddress->getPostcode());
            $customer->setBillingCity($billingAddress->getCity());
            $customer->setBillingCountry($billingAddress->getCountryId());
            $customer->setBillingPhone($billingAddress->getTelephone());
        }
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress !== null) {
            $customer->setShippingCompany($shippingAddress->getCompany());
            $customer->setShippingFirstName($shippingAddress->getFirstname());
            $customer->setShippingLastName($shippingAddress->getLastname());
            $customer->setShippingAddress1($shippingAddress->getStreet()[0]);
            $customer->setShippingPostcode($shippingAddress->getPostcode());
            $customer->setShippingCity($shippingAddress->getCity());
            $customer->setShippingCountry($shippingAddress->getCountryId());
        }

        $transaction->setCustomer($customer);

        $baseUrl = $this->urlBuilder->getRouteUrl('pgc');

        $transaction->setSuccessUrl($this->urlBuilder->getUrl('checkout/onepage/success'));
        $transaction->setCancelUrl($baseUrl . 'payment/redirect?reason=cancel');
        $transaction->setErrorUrl($baseUrl . 'payment/redirect?reason=error');

        if($this->pgcHelper->getPaymentConfigData(
            'callback_url',
            $paymentMethod,
            $this->storeManager->getStore()->getId()
        )){
            $transaction->setCallbackUrl($this->pgcHelper->getPaymentConfigData(
                'callback_url',
                $paymentMethod,
                $this->storeManager->getStore()->getId()
            ));
        } else
            {
            $transaction->setCallbackUrl($baseUrl . 'payment/callback');
        }


//        $this->prepare3dSecure2Data($transaction, $order);
        //file_put_contents('/var/www/ikari.aufbix.org/public_html/magento2/tran.txt', print_r($transaction, TRUE));
        switch ($transactionType) {
            case 'debit':
                $paymentResult = $client->debit($transaction);
                break;
            case 'preauth':
            default:
                $paymentResult = $client->preauthorize($transaction);
                break;
        }
        //file_put_contents('/var/www/ikari.aufbix.org/public_html/magento2/test.txt', print_r($paymentResult, TRUE));

        if (!$paymentResult->isSuccess()) {
            $response->setData([
                'type' => 'error',
                'errors' => $paymentResult->getFirstError()->getMessage()
            ]);
            return $response;
        }

        if ($paymentResult->getReturnType() == \Pgc\Client\Transaction\Result::RETURN_TYPE_ERROR) {

            // redundant? Type error should be covered by is success? Will it also have to restore quote in case of payment.js?

            $response->setData([
                'type' => 'error',
                'errors' => $paymentResult->getFirstError()->getMessage()
            ]);
            return $response;

        } elseif ($paymentResult->getReturnType() == \Pgc\Client\Transaction\Result::RETURN_TYPE_REDIRECT) {

            // case for HPP redirect or payment.js 3DS redirect

            $response->setData([
                'type' => 'redirect',
                'url' => $paymentResult->getRedirectUrl()
            ]);

            return $response;

        } elseif ($paymentResult->getReturnType() == \Pgc\Client\Transaction\Result::RETURN_TYPE_PENDING) {
            //payment is pending, wait for callback to complete

            //setCartToPending();

        } elseif ($paymentResult->getReturnType() == \Pgc\Client\Transaction\Result::RETURN_TYPE_FINISHED) {

            // missing result type handling success/error?
            // to be used for payment.js without 3D Secure redirect

            $response->setData([
                'type' => 'finished',
            ]);
        }

        return $response;
    }

    // doesn't handle preauth...

    private function prepare3dSecure2Data(Debit $transaction, Order $order)
    {
        $transaction->addExtraData('3ds:channel', '02'); // Browser
        $transaction->addExtraData('3ds:authenticationIndicator ', '01'); // Payment transaction

        if ($order->getCustomerIsGuest()) {
            $transaction->addExtraData('3ds:cardholderAuthenticationMethod', '01');
            $transaction->addExtraData('3ds:cardholderAccountAgeIndicator', '01');
        } else {
            $transaction->addExtraData('3ds:cardholderAuthenticationMethod', '02');
            //$transaction->addExtraData('3ds:cardholderAccountDate', \date('Y-m-d', $order->getCustomer()->getCreatedAtTimestamp()));
        }

        //$transaction->addExtraData('3ds:shipIndicator', \date('Y-m-d', $order->getCustomer()->getCreatedAtTimestamp()));

        if ($order->getShippigAddressId() == $order->getBillingAddressId()) {
            $transaction->addExtraData('3ds:billingShippingAddressMatch ', 'Y');
        } else {
            $transaction->addExtraData('3ds:billingShippingAddressMatch ', 'N');
        }

    }
}
