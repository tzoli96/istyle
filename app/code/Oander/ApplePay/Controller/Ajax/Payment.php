<?php


namespace Oander\ApplePay\Controller\Ajax;

class Payment extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $jsonHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
           // $validationData = $this->jsonHelper->jsonDecode($this->getRequest()->getContent());
            // $validationData['validationUrl']
            $session = $this->getAppleSession();
            //var_dump($session);die;

            return $this->jsonResponse($session);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            //$this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function getAppleSession(string $url = 'https://apple-pay-gateway-cert.apple.com/paymentservices/startSession'): string
    {
        $ch = curl_init();

        $data = [
           'merchantIdentifier' => 'merchant.iStyleTEST',
           'displayName' => 'iStyleTEST',
           'domainName' => 'dev.istyle.hu',
           'initiative' => 'web',
           'url'=> $url
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
       // curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'ECDHE-RSA-AES128-GCM-SHA256,ECDHE-ECDSA-AES128-SHA');
       // curl_setopt($ch, CURLOPT_SSL, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLCERT, '/var/www/ssh_keys/istyle_cz.pem');
        curl_setopt($ch, CURLOPT_SSLKEY, '/var/www/ssh_keys/istyle_cz.key');
        //curl_setopt($ch, CURLOPT_SSLKEYPASSWD, 'oander1234');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->jsonHelper->jsonEncode($data));

        //debug options
        //curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if( $result === false) {
            var_dump(curl_errno($ch) . " - " . curl_error($ch));
            var_dump($url);

        }
        // close cURL resource, and free up system resources

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        echo htmlspecialchars($verboseLog);
        $version = curl_version();
        var_dump($version);

        curl_close($ch);

        return $result;
    }
}
