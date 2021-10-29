<?php

namespace Oney\ThreeByFour\Model\Api\Payment;

use Magento\Sales\Api\Data\OrderInterface;
use Oney\ThreeByFour\Api\Payment\ConfirmInterface;
use Oney\ThreeByFour\Model\Api\ApiAbstract;
use Oney\ThreeByFour\Model\Method\Facilypay;

class Confirm extends ApiAbstract implements ConfirmInterface
{
    /**
     * @inheritDoc
     */
    public function confirm(OrderInterface $order)
    {
        $url = $this->_helperConfig->getUrlForStep('confirm');
        $formattedUrl = sprintf($url,
            $this->_helperConfig->getGeneralConfigValue('psp_guid', $order->getStoreId()),
            $this->_helperConfig->getGeneralConfigValue('merchant_guid', $order->getStoreId()),
            'CMDE|'.$order->getIncrementId()
        );

        $this->addHeader('X-Oney-Authorization', $this->_helperConfig->getApiConfigValue('api_payment', $order->getStoreId()))
            ->addHeader('X-Oney-Partner-Country-Code', $this->_helperConfig->getCountrySpecificationsConfigValue('country', $order->getStoreId()))
            ->addHeader('X-Oney-Secret', 'None');

        $this->setParams([
            "merchant_request_id" => $order->getIncrementId(),
            "payment" => [
                "payment_amount" => $order->getGrandTotal()
            ]
        ]);
        try {
            $response = json_decode($this->call('POST', $formattedUrl), true);
            $response = $response["purchase"]["status_code"] === Facilypay::STATUS_FUNDED;
        } catch (\Exception $e) {
            $response = false;
        }
        return $response;
    }
}
