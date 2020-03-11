<?php


namespace Oander\IstyleCustomization\Plugin\Magento\Payment\Gateway\Data\Order;

class OrderAdapter
{

    public function afterGetRemoteIp(
        \Magento\Payment\Gateway\Data\Order\OrderAdapter $subject,
        $result
    ) {
        $ips = explode(',', $result);
        if(count($ips)>0)
            $result = $ips[0];
        return $result;
    }
}