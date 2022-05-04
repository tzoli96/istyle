<?php

namespace Oander\AppleServices\Controller\Endpoint;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Oander\AppleServices\Helper\Config;
use Magento\Framework\App\Action\Action;
use Magento\Setup\Exception;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Controller\Result\JsonFactory;

class AjaxCall extends Action
{
    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var Curl
     */
    protected $curl;
    /**
     * @var Config
     */
    protected $helper;

    protected $requestCaptcha = null;

    protected $requestSecretKey = null;

    protected $requestUniqueID = null;

    protected $requestApiEndpoint = null;

    protected $requestReferralToken = null;

    /**
     * @param Context $context
     * @param Config $helper
     * @param Curl $curl
     * @param JsonFactory $jsonResultFactory
     */
    public function __construct(
        Context     $context,
        Config      $helper,
        Curl        $curl,
        JsonFactory $jsonResultFactory
    )
    {
        $this->curl = $curl;
        $this->helper = $helper;
        $this->jsonResultFactory = $jsonResultFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $result = $this->jsonResultFactory->create();

        if (!$post) {
            return $this->errorSend();
        }
        try {
            $this->validation($post);
        } catch (\Exception $e) {
            return $this->errorSend();
        }
        if ($this->helper->getIsTestMode()) {
            return $this->getMockData();
        }

        //Applemusic config
        $client_access_id = $this->requestUniqueID; //uinique ID
        $private_key = $this->requestSecretKey; //signature
        $request_url = $this->requestApiEndpoint; //API end point
        $method = "GET";
        $content_type = 'application/json';
        $content_md5 = '';
        $request_uri = preg_replace("/https?:\/\/[^,?\/]*/", "", $request_url);
        $timestamp = gmdate("D, d M Y H:i:s ") . "GMT";
// 'http method,content-type,content-MD5,request URI,timestamp'
        $canonical_string = implode(",", [$method, $content_type, $content_md5, $request_uri, $timestamp]);
        $signature = base64_encode(hash_hmac("sha256", $canonical_string, $private_key, true));
        $auth_header = 'APIAuth-HMAC-SHA256 ' . $client_access_id . ':' . $signature;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: ' . $auth_header,
            'Content-Type: ' . $content_type,
            'Date: ' . $timestamp
        ));
//curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($output, true);

        $result->setData($response);
        return $result;
    }

    /**
     * @return Json
     */
    protected function errorSend()
    {
        $result = $this->jsonResultFactory->create();
        $result->setData([
            "error" => 'Captcha is not valid'
        ]);
        return $result;
    }

    /**
     * @param $request
     * @return bool
     * @throws Exception
     */
    protected function validation($request)
    {
        $this->requestCaptcha = (isset($request['captcha'])) ? $request['captcha'] : null;
        $this->requestSecretKey = (isset($request['secret_key'])) ? $request['secret_key'] : null;
        $this->requestApiEndpoint = (isset($request['api_endpoint'])) ? $request['api_endpoint'] : null;
        $this->requestUniqueID = (isset($request['unique_id'])) ? $request['unique_id'] : null;
        $this->requestReferralToken = (isset($request['referral_token'])) ? $request['referral_token'] : null;

        $this->headerRequest();
        if (!is_string($this->requestCaptcha) || !(strlen($this->requestCaptcha) > 0)) {
            throw new Exception(__("Validation is failed"));
        }

        //GRecaptcha Validation
        $captchaResponse = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$this->helper->getGooglRecaptchaSecretKey()}&response={$this->requestCaptcha}"));
        if (!$captchaResponse->success == false) {
            throw new Exception(__("Google validation is failed"));
        }

        return true;
    }

    /**
     * @return array
     */
    private function headerRequest()
    {
        $response['method'] = "GET";
        $response['content_type'] = "application/json";
        $content_md5 = '';
        //Kérdés ide a endpoint végére kell-e a referral token
        $request_uri = preg_replace("/https?:\/\/[^,?\/]*/", "", $this->requestApiEndpoint);
        $response['timestamp'] = gmdate("D, d M Y H:i:s ") . "GMT";
        $canonical_string = implode(",", [$response['method'], $response['content_type'], $content_md5, $request_uri, $response['timestamp']]);
        $signature = base64_encode(hash_hmac("sha256", $canonical_string, $this->requestSecretKey, true));
        $response['auth_header'] = 'APIAuth-HMAC-SHA256 ' . $this->requestUniqueID . ':' . $signature;
        return $response;
    }

    /**
     * @return Json
     */
    private function getMockData()
    {
        $result = $this->jsonResultFactory->create();
        $mockData = [
            'code' => $this->generateRandomString(),
            'url' => $this->generateRandomString(),
            'redemption_url' => $this->generateRandomString(),
            'remaining' => $this->generateRandomString(),
            'end_date' => $this->generateRandomString(),
        ];
        $result->setData($mockData);
        return $result;
    }

    private function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }
}