<?php

namespace Oander\IstyleCheckout\Model;

use Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Store\Model\ScopeInterface;

class AgreementsConfigProvider extends \Magento\CheckoutAgreements\Model\AgreementsConfigProvider
{
    public function __construct(
        ScopeConfigInterface $scopeConfiguration,
        CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository,
        Escaper $escaper)
    {
        parent::__construct($scopeConfiguration, $checkoutAgreementsRepository, $escaper);
    }

    /**
     * Returns agreements config
     *
     * @return array
     */
    protected function getAgreementsConfig()
    {
        $agreementConfiguration = [];
        $isAgreementsEnabled = $this->scopeConfiguration->isSetFlag(
            AgreementsProvider::PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        $agreementsList = $this->checkoutAgreementsRepository->getList();
        $agreementConfiguration['isEnabled'] = (bool)($isAgreementsEnabled && count($agreementsList) > 0);

        foreach ($agreementsList as $agreement) {
            if($agreement->getAgreementType() === "checkout" || $agreement->getAgreementType()==null || $agreement->getAgreementType() === "all")
            {
                $agreementConfiguration['agreements'][] = [
                    'content' => $agreement->getIsHtml()
                        ? $agreement->getContent()
                        : nl2br($this->escaper->escapeHtml($agreement->getContent())),
                    'checkboxText' => $agreement->getCheckboxText(),
                    'mode' => $agreement->getMode(),
                    'agreementId' => $agreement->getAgreementId(),
                    'type' => $agreement->getAgreementType()
                ];
            }
        }

        return $agreementConfiguration;
    }
}