<?php

namespace Oney\ThreeByFour\Api\Marketing;

interface BusinessTransactionsInterface
{
    /**
     * @return array
     */
    public function getBusinessTransactions();

    /**
     * @return array
     */
    public function getActiveBusinessTransactions();
    /**
     * @return mixed
     */
    public function getResponse();
}
