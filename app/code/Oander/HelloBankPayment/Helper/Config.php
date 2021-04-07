<?php
namespace Oander\HelloBankPayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Oander\HelloBankPayment\Gateway\Config as HelperConfig;

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
     * @return string
     */
    public function getSucessPageMessage($storeid = null)
    {
        return $this->scopeConfig->getValue("payment/hellobank/successpage_message", ScopeInterface::SCOPE_STORE, $storeid);
    }

    /**
     * @param null $storeid
     * @return string
     */
    public function getHashKey($storeid = null)
    {
        return $this->scopeConfig->getValue("payment/hellobank/hash_key", ScopeInterface::SCOPE_STORE, $storeid);
    }

    /**
     * @param $storeid
     * @return mixed
     */
    public function getPaymentMethodSellerId($storeid = null)
    {
        return $this->scopeConfig->getValue("payment/hellobank/seller_id", ScopeInterface::SCOPE_STORE, $storeid);
    }

    /**
     * @param $request
     * @return array
     */
    public function getPaymentData($request,$type): array
    {
        $data = [
                'status'            =>  (isset($request["stav"])) ? $request["stav"]: null,
                'number'            =>  (isset($request["numwrk"])) ? $request["stav"]: null,
                'customer_name'     =>  (isset($request["jmeno"])) ? $request["jmeno"]: null,
                'receipts'          =>  (isset($request["prijmeni"])) ? $request["prijmeni"]: null,
                'payment'           =>  (isset($request["splatka"])) ? $request["splatka"]: null,
                'customer_number'   =>  (isset($request["numklient"])) ? $request["numklient"]: null,
                'order_number'      =>  (isset($request["obj"])) ? $request["obj"]: null,
            ];
        if($type == HelperConfig::HELLOBANK_REPONSE_TYPE_KO)
        {
            $data['seller_id']  = (isset($request['vdr'])) ? $request['vdr'] : null;
        } else {
            $data['id']         = (isset($request['numaut'])) ? $request['numaut'] : null;
        }

        return $data;
    }
}