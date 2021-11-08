<?php


namespace Oney\ThreeByFour\Model\Api\Payment;

use Magento\Sales\Api\Data\OrderInterface;
use Oney\ThreeByFour\Api\Payment\CancelInterface;
use Oney\ThreeByFour\Model\Api\ApiAbstract;

class Cancel extends ApiAbstract implements CancelInterface
{
    /**
     * @var int
     */
    protected $cancellation_reason_code = 0;

    /**
     * @var int
     */
    protected $cancellation_amount = 0;

    /**
     * @inheritDoc
     */
    public function cancel(OrderInterface $order)
    {

        $url = $this->_helperConfig->getUrlForStep('cancel');

        $this->addHeader('X-Oney-Authorization', $this->_helperConfig->getApiConfigValue('api_payment', $order->getStoreId()))
            ->addHeader('X-Oney-Partner-Country-Code', $this->_helperConfig->getCountrySpecificationsConfigValue('country', $order->getStoreId()))
            ->addHeader('X-Oney-Secret', 'None');

        $formattedUrl = sprintf($url,
            $this->_helperConfig->getGeneralConfigValue('psp_guid', $order->getStoreId()),
            $this->_helperConfig->getGeneralConfigValue('merchant_guid', $order->getStoreId()),
            'CMDE|'.$order->getIncrementId()
        );
        if(!$this->getCancellationAmount()) {
            $this->setCancellationAmount($order->getGrandTotal());
        }
        $purchase = [
            "cancellation_amount" => $this->getCancellationAmount(),
            "cancellation_reason_code" => $this->getCancellationReasonCode()
        ];
        if($this->getCancellationReasonCode()) {
            $purchase["refund_down_payment"] = 1;
        }
        $this->addParam("merchant_request_id", $order->getIncrementId())
            ->addParam("purchase", $purchase);
        try {
            return $this->call('POST', $formattedUrl);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function getCancellationReasonCode()
    {
        return $this->cancellation_reason_code;
    }

    /**
     * @inheritDoc
     */
    public function setCancellationReasonCode($cancellation_reason_code)
    {
        $this->cancellation_reason_code = $cancellation_reason_code;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCancellationAmount()
    {
        return $this->cancellation_amount;
    }

    /**
     * @inheritDoc
     */
    public function setCancellationAmount($cancellation_amount)
    {
        $this->cancellation_amount = $cancellation_amount;
        return $this;
    }
}
