<?php

namespace Oander\IstyleCustomization\Rewrite\Innobyte\MStart\Controller\Payment;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Innobyte\MStart\Ui\ConfigProvider;
use Innobyte\MStart\Api\Data\TransactionResponseInterface;
use Innobyte\MStart\Gateway\Helper\Data as PaymentDataHelper;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class Response
 * @package Oander\IstyleCustomization\Innobyte\MStart\Controller\Payment
 */
class Response extends \Innobyte\MStart\Controller\Payment\Response
{
    /**
     * @var OrderInterface
     */
    protected $order;

    /**
     * Response constructor.
     * @param CheckoutSession $checkoutSession
     * @param PaymentDataHelper $paymentDataHelper
     * @param OrderSender $orderSender
     * @param LoggerInterface $logger
     * @param OrderInterface $order
     * @param Context $context
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        PaymentDataHelper $paymentDataHelper,
        OrderSender $orderSender,
        LoggerInterface $logger,
        OrderInterface $order,
        Context $context
    ) {
        parent::__construct(
            $checkoutSession,
            $paymentDataHelper,
            $orderSender,
            $logger,
            $context
        );

        $this->order = $order;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $params = $this->getRequest()->getParams();
        if (!$order->getId() && isset($params['order_number'])) {
            try {
                $order = $this->order->loadByIncrementId($params['order_number']);
            } catch (\Exception $exception) {
                $this->logger->error('M-Start response exception: '.(string)$exception->getMessage(), $params);
            }
        }

        if (!$order->getId()) {
            return $this->_forward('noroute');
        }

        $payment = $order->getPayment();
        if ($payment->getMethod() != ConfigProvider::CODE) {
            return $this->_forward('noroute');
        }
        $this->logger->debug('M-Start payment response', $params);

        $responseHash = $params[TransactionResponseInterface::KEY_RESPONSE_HASH];
        unset(
            $params[TransactionResponseInterface::KEY_RESPONSE_HASH],
            $params[TransactionResponseInterface::KEY_RESPONSE_HASH_2]
        );
        if (!$this->paymentDataHelper->isResponseHashValid($params, $responseHash)) {
            return $this->_forward('noroute');
        }

        if ($params[TransactionResponseInterface::KEY_RESPONSE_RESULT] == TransactionResponseInterface::RESPONSE_RESULT_APPROVED) {
            $this->paymentDataHelper->processResponse($params, $order);
            $this->orderSender->send($order);

            return $this->_redirect('checkout/onepage/success');
        } else {
            $order
                ->cancel()
                ->save()
            ;
            $this->checkoutSession->restoreQuote();
            $message = $this->paymentDataHelper->getResponseMessage(
                $params[TransactionResponseInterface::KEY_RESPONSE_RESULT]
            );
            $this->messageManager->addErrorMessage($message);

            return $this->_redirect('checkout/cart');
        }
    }
}
