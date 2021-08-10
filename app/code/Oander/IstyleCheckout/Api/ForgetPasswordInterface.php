<?php
namespace Oander\IstyleCheckout\Api;

interface ForgetPasswordInterface
{
    CONST CUSTOMER_EMAIL_PARAM = "customerEmail";

    /**
     * @return string
     * @throws \Zend_Validate_Exception
     */
    public function execute();
}