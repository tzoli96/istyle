<?php
namespace Oander\RaiffeisenPayment\Controller\Payment;

use Oander\RaiffeisenPayment\Gateway\Config\ConfigValueHandler;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class Redirect extends Action
{

    const LOAN_URL = "https://www.cetelem.cz/cetelem2_webshop.php/zadost-o-pujcku/on-line-zadost-o-pujcku";

    /**
     * @var RequestBuild
     */
    private $requestBuild;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ConfigValueHandler
     */
    private $config;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;



    /**
     * Redirect constructor.
     *
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param ConfigValueHandler $config
     */
    public function __construct(
        JsonFactory $_resultJsonFactory,
        PageFactory $_resultPageFactory,
        Context $context,
        CheckoutSession $checkoutSession,
        ConfigValueHandler $config
    ) {
        $this->_resultJsonFactory = $_resultJsonFactory;
        $this->_resultPageFactory = $_resultPageFactory;
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
    }

    /**
     * @return RedirectResult
     */
    public function execute()
    {
        /** @var Order $order */
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order instanceof Order) {
            /** @var Payment $payment */
            $payment = $order->getPayment();
            return $this->requestBuild->execute($payment->getAdditionalInformation(),$order->getIncrementId());
        }

    }
}
