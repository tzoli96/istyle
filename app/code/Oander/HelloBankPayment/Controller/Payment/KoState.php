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

class KoState extends Action
{
    /**
     * @var HelperConfig
     */
    private $helperConfig;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var HelloBankModel
     */
    private $helloBankModel;


    /**
     * Processing constructor.
     *
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param HelloBankModel $helloBankModel
     * @param HelperConfig $helperConfig
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        HelloBankModel $helloBankModel,
        HelperConfig $helperConfig
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helloBankModel = $helloBankModel;
        $this->helperConfig = $helperConfig;
        parent::__construct($context);
    }

    /**
     * Initialize redirect to bank
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var Order $order */
        $order = $this->checkoutSession->getLastRealOrder();
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($order instanceof Order) {
            $returnData = $this->getRequest()->getParams();
            $this->helloBankModel->handleStatus(
                $order,
                $this->helperConfig->getPaymentData($returnData,Config::HELLOBANK_REPONSE_TYPE_KO),
                true
            );
            $resultRedirect->setPath('checkout/onepage/success');
        }

        return $resultRedirect;
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
