<?php

namespace Oander\IstyleCheckout\Block;

use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

class RegistrationAgreements extends \Magento\CheckoutAgreements\Block\Agreements
{
    public function __construct(
        Context $context,
        CollectionFactory $agreementCollectionFactory,
        array $data = []
    ){
        parent::__construct($context, $agreementCollectionFactory, $data);
    }

    /**
     * @return mixed
     */
    public function getAgreements()
    {
        if (!$this->hasAgreements()) {
            $agreements = [];
            if ($this->_scopeConfig->isSetFlag('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE)) {
                /** @var \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection $agreements */
                $agreements = $this->_agreementCollectionFactory->create();
                $agreements->addStoreFilter($this->_storeManager->getStore()->getId());
                $agreements->addFieldToFilter('is_active', 1);
                $agreements->addFieldToFilter('agreement_type', "registration");
            }
            $this->setAgreements($agreements);
        }
        return $this->getData('agreements');
    }

}