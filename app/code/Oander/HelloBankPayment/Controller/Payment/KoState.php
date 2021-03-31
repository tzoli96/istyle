<?php
namespace Oander\HelloBankPayment\Controller\Payment;

use Oander\HelloBankPayment\Model\HelloBank as HelloBankModel;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;
use Oander\HelloBankPayment\Gateway\Config;
use Oander\HelloBankPayment\Helper\Config as HelperConfig;
use Magento\Sales\Api\OrderRepositoryInterface;

class OkState extends Action
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var HelloBankModel
     */
    private $helloBankModel;

    /**
     * @var HelperConfig
     */
    private $helperConfig;


    /**
     * Processing constructor.
     *
     * @param HelperConfig $helperConfig
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param HelloBankModel $helloBankModel
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        HelperConfig $helperConfig,
        Context $context,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        HelloBankModel $helloBankModel
    ) {
        $this->orderRepository = $orderRepository;
        $this->helperConfig = $helperConfig;
        $this->checkoutSession = $checkoutSession;
        $this->helloBankModel = $helloBankModel;
        parent::__construct($context);
    }

    /**
     * Initialize redirect to bank
     *
     * @return Redirect
     * @throws LocalizedException
     */
    //
    public function execute()
    {
        $orderId=$this->getRequest()->getParam("obj");
        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);
        if ($order instanceof Order) {
            $returnData = $this->getRequest()->getParams();
            $returnData["state_type"] = "KO";
            $this->helloBankModel->handleStatus(
                $order,
                $this->helperConfig->getPaymentData($returnData,Config::HELLOBANK_REPONSE_TYPE_OK),
                true
            );
            return $this->_redirect('checkout/onepage/success', array('_query' => $returnData));
        }

    }

    /**
     * @param Order $order
     */
    private function failedPayment($order)
    {
        if ($order->getId() && $order->getState() != Order::STATE_CANCELED) {
            $comment = __('Failed HelloBank transaction');
            $order->cancel();
            $this->checkoutSession->restoreQuote();
            $this->messageManager->addErrorMessage($comment);
        }
    }
}
