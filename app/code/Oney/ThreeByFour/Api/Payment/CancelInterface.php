<?php

namespace Oney\ThreeByFour\Api\Payment;

use Magento\Sales\Api\Data\OrderInterface;
use Oney\ThreeByFour\Model\Api\Payment\Cancel;

interface CancelInterface
{
    /**
     * @param OrderInterface $order
     *
     * @return mixed
     */
    public function cancel(OrderInterface $order);

    /**
     * @return int
     */
    public function getCancellationReasonCode();

    /**
     * @param int $cancellation_reason_code
     *
     * @return Cancel
     */
    public function setCancellationReasonCode($cancellation_reason_code);

    /**
     * @return int
     */
    public function getCancellationAmount();

    /**
     * @param int $cancellation_amount
     *
     * @return Cancel
     */
    public function setCancellationAmount($cancellation_amount);
}
