<?php

namespace Oander\SalesforceLoyalty\Model\PointBlock;

use Oander\Salesforce\Helper\SoapClient as Client;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session\Proxy as CustomerSession;

class Helper
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var CustomerSession
     */
    protected $customer;

    /**
     * @param Client $client
     * @param CustomerSession $customer
     */
    public function __construct(
        Client          $client,
        CustomerSession $customer
    )
    {
        $this->client = $client;
        $this->customer = $customer;
    }

    /**
     * @param $reqPoint
     * @return bool
     */
    public function pointValidation($reqPoint)
    {
        if (!$this->customer->isLoggedIn()) {
            return false;
        }
        $customerPoint = $this->client->getCustomerAffiliatePoints($this->customer->getCustomer());
        /**
         * todo: admin config az elérhető maximum illetve lehet-e költeni v nem
         */
        return ($customerPoint >= $reqPoint) ? true : false;
    }

    /**
     * @param $reqPoint
     * todo: kérdés az, hogy customer nem fog-e be cachelni (valami megoldás volt arra, h ne cacheljen be asszem ha Proxy-t használunk?)
     */
    public function pointBlock($reqPoint)
    {
        if (!$this->customer->isLoggedIn()) {
            return false;
        }
        try {
            return ($this->pointValidation($reqPoint))
                ?  $this->client->BlockAffiliateMembershipPoints($this->customer->getCustomer(),$reqPoint)
                : false;

        } catch (\Exception $e)
        {
            return false;
        }
    }
}