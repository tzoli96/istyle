<?php

namespace Pgc\Pgc\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Pgc\Pgc\Helper\Data;

class Callback extends Action
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Data
     */
    private $pgcHelper;


    public function __construct(
        Context $context,
        OrderInterfaceFactory $orderFactory,
        Data $pgcHelper
    ) {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->pgcHelper = $pgcHelper;
    }

    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $notification = $request->getContent();

        if ($request->getMethod() !== 'POST') {
            die('invalid request');
        }

        $xml = \simplexml_load_string($notification);
        $data = \json_decode(json_encode($xml),true);

        if (empty($data)) {
            die('invalid request');
        }

        $incrementId = substr($data['transactionId'], 8);
        //file_put_contents('/var/www/ikari.aufbix.org/public_html/magento2/callbackorder.txt', print_r($incrementId, TRUE));
        $amount  = $data['amount'];

        /** @var Order $order */
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);

        // should use die?
        if (empty($order->getId())) {
            return false;
        }
        //TODO: SELECT CORRECT PAYMENT SETTINGS
        \Pgc\Client\Client::setApiUrl($this->pgcHelper->getGeneralConfigData('host'));
        $client = new \Pgc\Client\Client(
            $this->pgcHelper->getGeneralConfigData('username'),
            $this->pgcHelper->getGeneralConfigData('password'),
            $this->pgcHelper->getPaymentConfigData('api_key', 'pgc_creditcard', null),
            $this->pgcHelper->getPaymentConfigData('shared_secret', 'pgc_creditcard', null)
        );

        $queryString = $request->getServerValue('QUERY_STRING');
        if (empty($request->getHeader('date')) ||
            empty($request->getHeader('authorization')) ||
            $client->validateCallback($notification, $queryString, $request->getHeader('date'), $request->getHeader('authorization'))) {

            die('invalid callback');
        }

        if($data['result'] == 'ERROR') {
            if($data['transactionType'] == 'DEBIT' || $data['transactionType'] == 'PREAUTHORIZE')  
            { 
                //file_put_contents('/var/www/ikari.aufbix.org/public_html/magento2/callback.txt', print_r($data, TRUE));
                // order->cancel would also trigger a payment->cancel, note it also triggers an event
                $order->registerCancellation("Payment failed.");
                $order->save();
            }
        }
        else if($data['result'] == 'OK') {
            if($data['transactionType'] == 'DEBIT' || $data['transactionType'] == 'PREAUTHORIZE')  
            {
                $order->setState(Order::STATE_PROCESSING);
                $order->setStatus(Order::STATE_PROCESSING);
                $payment = $order->getPayment();
                $payment->setAmountAuthorized($order->getTotalDue());
                $payment->setCcOwner(strtoupper($data['returnData']['creditcardData']['cardHolder']));
                $payment->setCcType(strtoupper($data['returnData']['creditcardData']['type']));
                $payment->setCcLast4($data['returnData']['creditcardData']['lastFourDigits']);
                $payment->setCcExpMonth($data['returnData']['creditcardData']['expiryMonth']);
                $payment->setCcExpYear($data['returnData']['creditcardData']['expiryYear']);
                $payment->setTransactionId($data['referenceId']);
                
                switch ($data['transactionType']) {
                    case 'DEBIT':
                        $payment->setBaseAmountAuthorized($order->getBaseTotalDue());
                        $payment->registerCaptureNotification($amount, false);
                        break;
                    case 'PREAUTHORIZE':
                        $payment->setIsTransactionClosed(0);
                        $payment->authorize(false, $amount); // false = offline = registerAuthorizationNotification
                        break;
                }
                $payment->save();
                $order->save();
            }
        }

        die('OK');
    }
}
