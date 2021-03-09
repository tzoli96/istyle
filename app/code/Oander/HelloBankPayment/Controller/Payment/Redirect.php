<?php
namespace Oander\HelloBankPayment\Controller\Payment;

use Magento\Framework\App\ResponseInterface;
use Oander\HelloBankPayment\Gateway\Config\ConfigValueHandler;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Oander\HelloBankPayment\Gateway\Config as ConfigHelper;

class Redirect extends Action
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ConfigValueHandler
     */
    private $config;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $param = [];

    /**
     * Redirect constructor.
     *
     * @param Context         $context
     * @param CheckoutSession $checkoutSession
     * @param ConfigValueHandler          $config
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        ConfigValueHandler $config
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
    }

    /**
     * @return ResponseInterface
     */
    public function execute()
    {

        /** @var Order $order */
        $order = $this->checkoutSession->getLastRealOrder();

        if ($order instanceof Order) {
            /** @var Payment $payment */
            $payment = $order->getPayment();
            $paymentAdditionalInformation = $payment->getAdditionalInformation();
            switch ($paymentAdditionalInformation['response'])
            {
                case ConfigHelper::HELLOBANK_REPONSE_TYPE_KO:
                    $this->path = "hellobank/payment/kostate";
                    $this->param = [
                        'stav'          => 2,
                        'vdr'           => 2,
                        'numwrk'        => 1,
                        'jmeno'         => 1,
                        'prijmeni'      => 1,
                        'splatka'       => 1,
                        'numklient'     => 1,
                        'obj'           => $order->getIncrementId(),
                    ];

                    break;

                case ConfigHelper::HELLOBANK_REPONSE_TYPE_OK:
                    $this->path = "hellobank/payment/okstate";
                    $this->param = [
                        'stav'          => 1,
                        'numaut'        => 2,
                        'numwrk'        => 1,
                        'jmeno'         => 1,
                        'prijmeni'      => 1,
                        'splatka'       => 1,
                        'numklient'     => 1,
                        'obj'           => $order->getIncrementId(),
                    ];
            }
        }
        return $this->_redirect($this->path, $this->param);
    }
}
