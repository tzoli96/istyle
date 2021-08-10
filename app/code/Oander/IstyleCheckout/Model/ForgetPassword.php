<?php

namespace Oander\IstyleCheckout\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Escaper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Oander\IstyleCheckout\Api\ForgetPasswordInterface;
use Magento\Framework\Json\Helper\Data;

class ForgetPassword implements ForgetPasswordInterface
{

    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var Escaper */
    protected $escaper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param Escaper $escaper
     * @param RequestInterface $request
     * @param Data $jsonHelper
     */
    public function __construct(
        Session                    $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper                    $escaper,
        RequestInterface           $request,
        Data                       $jsonHelper
    )
    {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->escaper = $escaper;
        $this->request = $request;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @return string
     * @throws \Zend_Validate_Exception
     */
    public function execute()
    {
        $customerEmail = $this->request->getParam(self::CUSTOMER_EMAIL_PARAM);
        $resultData = [];
        if ($customerEmail) {
            if (!\Zend_Validate::is($customerEmail, 'EmailAddress')) {
                $this->session->setForgottenEmail($customerEmail);
                $resultData = [
                    "status" => "success",
                    "message" => __('Please correct the email address.')
                ];
            }
            try {
                $this->customerAccountManagement->initiatePasswordReset(
                    $customerEmail,
                    AccountManagement::EMAIL_RESET
                );
                $resultData = [
                    "status" => "success",
                    "message" => "OK"
                ];
            } catch (NoSuchEntityException $exception) {
                $resultData = [
                    "status" => "success",
                    "message" => $exception->getMessage()
                ];

            } catch (SecurityViolationException $exception) {
                $resultData = [
                    "status" => "success",
                    "message" => $exception->getMessage()
                ];
            } catch (\Exception $exception) {
                $resultData = [
                    "status" => "success",
                    "message" => __('We\'re unable to send the password reset email.')
                ];
            }
        } else {
            $resultData = [
                "status" => "success",
                "message" => __('Please enter your email.')
            ];
        }

        return $this->jsonHelper->jsonEncode($resultData);
    }
}