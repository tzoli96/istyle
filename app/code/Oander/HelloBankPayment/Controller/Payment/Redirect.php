<?php
namespace Oander\HelloBankPayment\Controller\Payment;

use Oander\HelloBankPayment\Gateway\Config\ConfigValueHandler;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Oander\HelloBankPayment\Helper\Config as PaymentHelper;
use Oander\HelloBankPayment\Model\HelloBank;
use Oander\HelloBankPayment\Helper\RequestBuild;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class Redirect extends Action
{
    /**
     * @var RequestBuild
     */
    private $requestBuild;
    /**
     * @var HelloBank
     */
    private $helloBankService;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ConfigValueHandler
     */
    private $config;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

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
     * @param HelloBank $helloBankService
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param ConfigValueHandler $config
     * @param PaymentHelper $paymentHelper
     * @param RequestBuild $requestBuild
     */
    public function __construct(
        JsonFactory $_resultJsonFactory,
        PageFactory $_resultPageFactory,
        HelloBank $helloBankService,
        Context $context,
        CheckoutSession $checkoutSession,
        ConfigValueHandler $config,
        PaymentHelper $paymentHelper,
        RequestBuild $requestBuild
    ) {
        $this->_resultJsonFactory = $_resultJsonFactory;
        $this->_resultPageFactory = $_resultPageFactory;
        $this->helloBankService = $helloBankService;
        $this->paymentHelper = $paymentHelper;
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->requestBuild = $requestBuild;
    }

    /**
     * @return RedirectResult
     */
    public function execute()
    {
        /** @var RedirectResult $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('checkout/onepage/success');
        /** @var Order $order */
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order instanceof Order) {
            /** @var Payment $payment */
            $payment = $order->getPayment();
            $this->_redirect("https://www.cetelem.cz/cetelem2_webshop.php/zadost-o-pujcku/on-line-zadost-o-pujcku",
                $this->requestBuild->paramsMake($payment->getAdditionalInformation(),$order->getIncrementId()) );
        }

    }
}
