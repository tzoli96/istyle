<?php
namespace Oander\HelloBankPayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const AVAILABLE_BAREMS_URL = "https://www.cetelem.cz/webciselnik2.php?kodProdejce=";

    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @param $storeid
     * @return bool
     */
    public function getPaymnetMethodIsActive($storeid = null)
    {
        return $this->scopeConfig->getValue("payment/hellobank/active", ScopeInterface::SCOPE_STORE, $storeid);
    }

    /**
     * @param $storeid
     * @return mixed
     */
    public function getPaymentMethodSellerId($storeid = null)
    {
        return $this->scopeConfig->getValue("payment/hellobank/seller_id", ScopeInterface::SCOPE_STORE, $storeid);
    }
}