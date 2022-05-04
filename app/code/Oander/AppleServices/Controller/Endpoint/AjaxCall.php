<?php

namespace Oander\AppleServices\Controller\Endpoint;

use Magento\Framework\App\Action\Context;
use Oander\AppleServices\Helper\Config;
use Magento\Framework\App\Action\Action;
use Magento\Setup\Exception;
use Magento\Framework\HTTP\Client\Curl;

class AjaxCall extends Action
{
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
     */
    public function __construct(
        Context $context,
        Config  $helper,
        Curl    $curl
    )
    {
        $this->curl = $curl;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|string|void
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();

        if (!$post) {
            return $this->errorSend();
        }
        try {
            $this->validation($post);
        } catch (\Exception $e) {
            return $this->errorSend();
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

        return $output;
    }

    /**
     * @return false|string
     */
    protected function errorSend()
    {
        return json_encode(
            [
                "error" => true,
                "message" => "Captcha is not valid"
            ]
        );
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
}