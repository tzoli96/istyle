<?php

namespace Oander\IstyleCheckout\Block;

use Magento\CheckoutAgreements\Block\Agreements;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Oander\SalesforceLoyalty\Helper\Config;

class RegistrationAgreements extends Agreements
{
    /**
     * @var Config
     */
    private $helper;

    /**
     * @param Context $context
     * @param CollectionFactory $agreementCollectionFactory
     * @param Config $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $agreementCollectionFactory,
        Config $helper,
        array $data = []
    ){
        $this->helper = $helper;
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
                $agreements->addFieldToFilter('agreement_type', [
                    ['eq'    => 'registration'],
                    ['eq'    => 'all'],
                    ['eq'    => 'loyalty']
                ]);
            }
            $this->setAgreements($agreements);
        }
        return $this->getData('agreements');
    }

    /**
     * @return int
     */
    public function getRegistrationType(): int
    {
        return $this->helper->getRegistrationTermType();
    }

}