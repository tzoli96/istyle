<?php

namespace Oander\SalesforceLoyalty\Model;

class PointFreeQueueClass extends \Oander\Queue\Model\JobClass
{
    const NAME_PREFIX = "loyaltypointfree";

    const DATA_TRANSACTIONID = "transaction_id";
    const DATA_ORDERINCREMENTID = "order_increment_id";
    const DATA_COUNTRYCODE = "country_code";

    /**
     * @var \Oander\Salesforce\Model\Endpoint\Loyalty
     */
    private $loyaltyEndpoint;

    /**
     * @param \Oander\Salesforce\Model\Endpoint\Loyalty $loyaltyEndpoint
     * @param array $data
     */
    public function __construct(
        \Oander\Salesforce\Model\Endpoint\Loyalty $loyaltyEndpoint,
        array $data = []
    )
    {
        parent::__construct($data);
        $this->loyaltyEndpoint = $loyaltyEndpoint;
    }

    public function execute(): bool
    {
        $this->_validateData();
        try {
            $return = $this->loyaltyEndpoint->UpdateAffiliateTransaction($this->getData(self::DATA_TRANSACTIONID), \Oander\Salesforce\Model\Endpoint\Loyalty::LOYALTY_UPDATETRANSACTION_TYPE_BLOCKEDCANCELLED, $this->getData(self::DATA_COUNTRYCODE));
            $this->output = \Zend_Json::encode($return);
            $this->hasError = false;
            return true;
        }
        catch (\Oander\Salesforce\Exception\RESTResponseException $exception)
        {
            $this->input = $exception->getRequest();
            $this->output = $exception->getResponse();
            $this->hasError = false;
        }
        catch (\Oander\Salesforce\Exception\RESTException $exception)
        {
            $this->input = $exception->getRequest();
            $this->output = $exception->getResponse();
            $this->hasError = false;
        }
        catch (\Exception $exception)
        {
            $this->hasError = true;
        }
        return false;
    }

    public function getName(): string
    {
        $this->_validateData();
        return implode(self::NAME_SEPARATOR, [
            self::NAME_PREFIX,
            $this->getData(self::DATA_ORDERINCREMENTID),
            $this->getData(self::DATA_TRANSACTIONID)
        ]);
    }

    public function getRetriesCount(): int
    {
        return 3;
    }

    private function _validateData()
    {
        if(!(
            is_string($this->getData(self::DATA_ORDERINCREMENTID)) &&
            is_string($this->getData(self::DATA_TRANSACTIONID)) &&
            is_string($this->getData(self::DATA_COUNTRYCODE))
        ))
            throw new \InvalidArgumentException(__("PointFreeQueueClass missing parameter"));
    }
}