<?php

namespace Oander\IstyleCustomization\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Oander\Salesforce\Helper\SoapClient;

class Index extends Action
{
    /**
     * @var SoapClient
     */
    private $soapClient;

    public function __construct(
        SoapClient $soapClient,
        Context $context
    ) {
        $this->soapClient = $soapClient;
        parent::__construct($context);
    }

    public function execute()
    {
        var_dump($this->soapClient->getCustomerAffiliatePoints());
        die();
        // TODO: Implement execute() method.
    }
}