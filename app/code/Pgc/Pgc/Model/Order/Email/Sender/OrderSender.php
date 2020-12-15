<?php
namespace Pgc\Pgc\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender as OriginalClass;

class OrderSender extends OriginalClass
{

    public function send(Order $order, $forceSyncMode = false)
    {
        $payment = $order->getPayment()->getMethodInstance()->getCode();

        if($forceSyncMode){
            $this->checkAndSendMail($order);
        }

        if($payment == 'pgc_creditcard')
        {
            return false;
        }

        if (!$this->globalConfig->getValue('sales_email/general/async_sending')  ) {
            $this->checkAndSendMail($order);
        }

        $this->orderResource->saveAttribute($order, 'send_email');

        return false;
    }

    /**
     * @param Order $order
     * @return bool|void
     * @throws \Exception
     */
    private function checkAndSendMail(Order $order)
    {
        if ($this->checkAndSend($order)) {
            $order->setEmailSent(true);
            $this->orderResource->saveAttribute($order, ['send_email', 'email_sent']);
            return true;
        }
    }

}