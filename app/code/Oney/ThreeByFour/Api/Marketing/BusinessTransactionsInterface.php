<?php

namespace Oney\ThreeByFour\Api\Marketing;

interface BusinessTransactionsInterface
{
    /**
     * @param int|null $store
     * @return array
     */
    public function getBusinessTransactions($store = null);

    /**
     * @param int|null $store
     * @return array
     */
    public function getActiveBusinessTransactions($store = null);
    /**
     * @return mixed
     */
    public function getResponse();
}
