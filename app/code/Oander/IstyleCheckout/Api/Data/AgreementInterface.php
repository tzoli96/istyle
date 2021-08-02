<?php

namespace Oander\IstyleCheckout\Api\Data;
use Magento\CheckoutAgreements\Api\Data\AgreementInterface as exetendedInterface;

interface AgreementInterface extends exetendedInterface
{
    const AGREEMENT_TYPE = 'agreement_type';

    /**
     * @return mixed
     */
    public function getAgreementType();

    /**
     * @param $agreementType
     * @return mixed
     */
    public function setAgreementType($agreementType);

}