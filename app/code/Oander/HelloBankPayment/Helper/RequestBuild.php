<?php
namespace Oander\HelloBankPayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Client\Curl;

class RequestBuild extends AbstractHelper
{
    /**
     * @var Curl
     */
    private $curl;

    public function __construct(
        Curl $curl,
        Context $context
    ) {
        parent::__construct($context);
        $this->curl = $curl;
    }

    public function paramsMake($params)
    {
        $res = [
                'kodProdejce'       => 'value1',
                'kodBaremu'         => $params['kodBaremu'],
                'kodPojisteni'      => $params['kodPojisteni'],
                'cenaZbozi'         => $params['cenaZbozi'],
                'primaPlatba'       => $params['primaPlatba'],
                'vyseUveru'         => $params['vyseUveru'],
                'pocetSplatek'      => $params['pocetSplatek'],
                'odklad'            => $params['odklad'],
                'vyseSplatky'       => $params['vyseSplatky'],
                'cenaUveru'         => $params['cenaUveru'],
                'RPSN'              => $params['RPSN'],
                'ursaz'             => $params['ursaz'],
                'celkovaCastka'     => $params['celkovaCastka'],
                'recalc'            => 0,
                'url_back_ok'       => 'http://czech.istyledev.test/hellobank/payment/okstate/',
                'url_back_ko'       => 'http://czech.istyledev.test/hellobank/payment/kostate/',
            ];

        return $res;
    }
}