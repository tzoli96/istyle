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

class Processing extends Action
{
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
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        HelloBankModel $helloBankModel
    ) {
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
    public function execute()
    {
        /** @var Order $order */
        $order = $this->checkoutSession->getLastRealOrder();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($order instanceof Order) {
            $this->helloBankModel->handleStatus($order);
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
