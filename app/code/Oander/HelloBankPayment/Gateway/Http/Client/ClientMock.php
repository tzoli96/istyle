<?php
namespace Oander\HelloBankPayment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Oander\HelloBankPayment\Gateway\Config;
use Oander\HelloBankPayment\Gateway\Request\MockDataRequest;

class ClientMock implements ClientInterface
{
    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        switch ($this->getUrlType($transferObject))
        {
            case Config::HELLOBANK_REPONSE_TYPE_OK:
                $response = [
                    'path'          => 'hellobank/payment/okstate',
                    'stav'          => 1,
                    'numaut'        => $this->generateTxnId(),
                    'numwrk'        => 1,
                    'jmeno'         => 1,
                    'prijmeni'      => 1,
                    'splatka'       => 1,
                    'numklient'     => 1,
                    'obj'           => $transferObject->getBody()['ORDER_ID'],
                ];
                break;

            case Config::HELLOBANK_REPONSE_TYPE_KO:
                $response = [
                    'path'          => 'hellobank/payment/kostate',
                    'stav'          => 2,
                    'vdr'           => 2,
                    'numwrk'        => 1,
                    'jmeno'         => 1,
                    'prijmeni'      => 1,
                    'splatka'       => 1,
                    'numklient'     => 1,
                    'obj'           => $transferObject->getBody()['ORDER_ID'],
                ];
            default:
        }

        return $response;
    }

    /**
     * @return string
     */
    protected function generateTxnId()
    {
        return md5(mt_rand(0, 1000));
    }

    /**
     * Returns result code
     *
     * @param TransferInterface $transfer
     * @return int
     */
    private function getUrlType(TransferInterface $transfer)
    {
        $headers = $transfer->getHeaders();

        if (isset($headers[MockDataRequest::HELLO_BANK_URL])) {
            return (int)$headers[MockDataRequest::HELLO_BANK_URL];
        }

        return mt_rand(1, 2);
    }


}
