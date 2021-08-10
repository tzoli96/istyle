<?php
namespace Oander\IstyleCheckout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class MageplazaRecaptcha extends \Mageplaza\GoogleRecaptcha\Helper\Data
{
    /**
     * @return array|false|false[]|\Magento\Framework\Phrase|mixed
     */
    public function checkCaptcha($storeId = null)
    {
        if (!$this->isEnabled($storeId) || !$this->getConfigFrontend('enabled', $storeId)) {
            return true;
        }

        $result = false;
        $checkResponse = 1;

        foreach ($this->getFormPostPaths($storeId) as $item) {
            if ($item != "" && strpos($this->_request->getRequestUri(), trim($item, " ")) !== false) {
                $checkResponse = 0;
                if ($this->_request->getParam('g-recaptcha-response') !== null) {
                    $response = $this->verifyResponse(null,null,$storeId);
                    if (isset($response['success']) && $response['success']) {
                        return $response['success'];
                    }
                }
            }
        }
        if ($checkResponse == 1 && $this->_request->getParam('g-recaptcha-response') !== null) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param null $storeId
     * @return array|mixed
     */
    public function isCaptchaFrontend($storeId = null)
    {
        if (!$this->isEnabled($storeId)) {
            return false;
        }

        return $this->getConfigFrontend('enabled', $storeId);
    }

    /**
     * @param null $storeId
     * @return array|mixed
     */
    public function getFormPostPaths($storeId = null)
    {
        $data = [];
        foreach ($this->_formPaths->defaultForms() as $key => $value) {
            if (in_array($key, $this->getFormsFrontend($storeId))) {
                $data[] = $value;
            }
        }
        $custom = explode("\n", str_replace("\r", "", $this->getConfigFrontend('custom/paths', $storeId)));
        if (!$custom) {
            return $data;
        }

        return array_merge($data, $custom);
    }

    /**
     * get reCAPTCHA server response
     *
     * @param null $recaptcha
     * @return array
     */
    public function verifyResponse($end = null, $recaptcha = null, $storeId = null)
    {
        $result = ['success' => false];

        $recaptcha = $recaptcha ?: $this->_request->getParam('g-recaptcha-response');
        if (!$recaptcha) {
            $result['message'] = __('The response parameter is missing.');

            return $result;
        }
        try {
            $recaptchaClass = new \ReCaptcha\ReCaptcha($end ? $this->getVisibleSecretKey($storeId) : $this->getInvisibleSecretKey($storeId));
            $resp           = $recaptchaClass->verify($recaptcha, $this->_request->getClientIp());
            if ($resp) {
                if ($resp->isSuccess()) {
                    $result['success'] = true;
                } else {
                    $result['message'] = __('The request is invalid or malformed.');
                }
            } else {
                $result['message'] = __('The request is invalid or malformed.');
            }
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
        }

        return $result;
    }
}