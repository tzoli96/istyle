<?php

namespace Oander\SalesforceLoyalty\Block\SuccessPage;

use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Oander\SalesforceLoyalty\Helper\Config;

class Agreements extends Template
{
    /**
     * @var Config
     */
    protected $helper;

    /**
     * @var CollectionFactory
     */
    protected $_agreementCollectionFactory;

    /**
     * @param Context $context
     * @param Config $helper
     * @param CollectionFactory $agreementCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $helper,
        CollectionFactory $agreementCollectionFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->_agreementCollectionFactory = $agreementCollectionFactory;
        parent::__construct($context, $data);
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
                if($this->helper->getRegistrationTermType() && $this->helper->getLoyaltyServiceEnabled())
                {
                    $agreementsType[] = [
                        ['eq'    => 'loyalty']
                    ];
                }else{
                    $agreementsType = [
                        ['eq'    => 'registration'],
                        ['eq'    => 'all'],
                    ];
                }

                $agreements->addFieldToFilter('agreement_type',$agreementsType);
            }
            $this->setAgreements($agreements);
        }
        return $this->getData('agreements');
    }
}