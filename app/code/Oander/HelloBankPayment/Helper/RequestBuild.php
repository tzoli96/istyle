<?php
namespace Oander\HelloBankPayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;
use Oander\HelloBankPayment\Gateway\Config\ConfigValueHandler;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Response\RedirectInterface;

class RequestBuild extends AbstractHelper
{
    const LOAN_URL = "https://www.cetelem.cz/cetelem2_webshop.php/zadost-o-pujcku/on-line-zadost-o-pujcku";
    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var ConfigValueHandler
     */
    private $configHandler;

    /**
     * @var Curl
     */
    protected $curlClient;

    /**
     * @var RedirectInterface
     */
    protected $redirect2;

    /**
     * RequestBuild constructor.
     * @param ConfigValueHandler $configHandler
     * @param UrlInterface $urlInterface
     * @param Context $context
     */
    public function __construct(
        ConfigValueHandler $configHandler,
        UrlInterface $urlInterface,
        Context $context,
        Curl $curlClient,
        RedirectInterface $redirect2
    ) {
        parent::__construct($context);
        $this->urlInterface = $urlInterface;
        $this->configHandler = $configHandler;
        $this->curlClient = $curlClient;
        $this->redirect2 = $redirect2;
    }

    /**
     * @param $params
     * @param $incrementId
     * @return array
     */
    public function paramsMake($params,$incrementId)
    {
        $res = [
                'kodProdejce'       => $this->configHandler->getSellerId(),
                'kodBaremu'         => $params['kodBaremu'],
                'kodPojisteni'      => $params['kodPojisteni'],
                'cenaZbozi'         => $params['cenaZbozi'],
                'ursaz'             => $params['ursaz'],
                'celkovaCastka'     => $params['celkovaCastka'],
                'recalc'            => 0,
                'url_back_ok'       => $this->urlInterface->getUrl("hellobank/payment/okstate/"),
                'url_back_ko'       => $this->urlInterface->getUrl("hellobank/payment/kostate/"),
                'obj'               => $incrementId,
            ];
        return $res;
    }

    /**
     * @param $param
     * @param $incrementId
     * @return string
     */
    public function execute($param,$incrementId)
    {

        $this->curlClient->post(self::LOAN_URL, $this->paramsMake($param,$incrementId));

        return $this->curlClient->getBody();
    }
}