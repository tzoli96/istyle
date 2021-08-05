<?php

namespace Oander\IstyleCheckout\Model;
use Magento\CheckoutAgreements\Model\Agreement as extendedClass;
use Oander\IstyleCheckout\Api\Data\AgreementInterface;

class Agreement extends extendedClass implements AgreementInterface
{
    /**
     * @inheritdoc
     */
    public function getAgreementType()
    {
        return $this->getData(self::AGREEMENT_TYPE);
    }
    /**
     * @inheritdoc
     */
    public function setAgreementType($agreementType)
    {
        return $this->setData(self::AGREEMENT_TYPE, $agreementType);
    }
}