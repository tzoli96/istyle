<?php

namespace Oander\IstyleCheckout\Block;

use Magento\CheckoutAgreements\Block\Agreements;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Oander\SalesforceLoyalty\Helper\Config;
use Oander\SalesforceLoyalty\Helper\Config as LoyaltyHelper;

class RegistrationAgreements extends Agreements
{
    /**
     * @var LoyaltyHelper
     */
    private $loyaltyHelper;
    /**
     * @var Config
     */
    private $helper;

    /**
     * @param Context $context
     * @param CollectionFactory $agreementCollectionFactory
     * @param Config $helper
     * @param LoyaltyHelper $loyaltyHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $agreementCollectionFactory,
        Config $helper,
        LoyaltyHelper $loyaltyHelper,
        array $data = []
    ){
        $this->loyaltyHelper = $loyaltyHelper;
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
                $agreementsType = [
                    ['eq'    => 'registration'],
                    ['eq'    => 'all'],
                ];
                if($this->loyaltyHelper->getRegistrationTermType() && $this->loyaltyHelper->getLoyaltyServiceEnabled())
                {
                    $agreementsType[] = [
                        ['eq'    => 'loyalty']
                    ];
                }

                $agreements->addFieldToFilter('agreement_type',$agreementsType);

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