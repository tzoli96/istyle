<?php

namespace Oander\SalesforceLoyalty\Block\SuccessPage;

use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

class Agreements extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $_agreementCollectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $agreementCollectionFactory
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        CollectionFactory $agreementCollectionFactory,
        array $data = []
    ) {
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
                $agreementsType[] = [
                    ['eq'    => 'loyalty']
                ];
                $agreements->addFieldToFilter('agreement_type',$agreementsType);
            }
            $this->setAgreements($agreements);
        }
        return $this->getData('agreements');
    }
}