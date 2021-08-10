<?php

namespace Oander\IstyleCheckout\Model;

use Magento\CheckoutAgreements\Model\AgreementModeOptions;
use Magento\Store\Model\ScopeInterface;

class AgreementsProvider extends \Magento\CheckoutAgreements\Model\AgreementsProvider
{

    /**
     * Get list of required Agreement Ids
     *
     * @return int[]
     */
    public function getRequiredAgreementIds()
    {
        $agreementIds = [];
        if ($this->scopeConfig->isSetFlag(self::PATH_ENABLED, ScopeInterface::SCOPE_STORE)) {
            $agreementCollection = $this->agreementCollectionFactory->create();
            $agreementCollection->addStoreFilter($this->storeManager->getStore()->getId());
            $agreementCollection->addFieldToFilter('is_active', 1);
            $agreementCollection->addFieldToFilter('agreement_type', 'checkout');
            $agreementCollection->addFieldToFilter('mode', AgreementModeOptions::MODE_MANUAL);
            $agreementIds = $agreementCollection->getAllIds();
        }
        return $agreementIds;
    }
}