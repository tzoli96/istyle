<?php
/**
 * Please fill before use, recaptha key found in magento admin, it's secret need to be inserted here
 */
//recaptcha config
$secretkey = '6LeuXp0UAAAAANPFdzdXdMAgb1rIkHX46Od8Ck3Z'; //secret key of recaptcha

//Applemusic config
$client_access_id = '5aa94f276193a765adcca3c5'; //uinique ID
$private_key = '3l6A2NevXXQsXIUEMr8ugjsVu5OJEKALs9UYuUpq55JOi06RhG/zXNOtA0amn8UUs5vzix66PBWQ+gp8Ls4qaQ=='; //signature
$request_url = "https://redeem.itunes.apple.com/api/nUSb5w/KwxQnw"; //API end point

/**
 * End of config
 */


if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response=$_POST["captcha"];

    if(!is_string($response)) {
        sendError();
        return;
    }
    if(!(strlen($response)>0))
    {
        sendError();
        return;
    }

    $verify=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretkey}&response={$response}");
    $captcha_success=json_decode($verify);
    if ($captcha_success->success==false) {
        sendError();
        return;
    }
    else if ($captcha_success->success==true) {


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
        echo $output;
    }
}

function sendError()
{
    echo json_encode(
        [
            "error" => true,
            "message" => "Captcha is not valid"
        ]
    );
}