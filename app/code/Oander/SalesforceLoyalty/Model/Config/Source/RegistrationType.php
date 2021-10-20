<?php

namespace Oander\SalesforceLoyalty\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Framework\App\RequestInterface;

class RegistrationType implements OptionSourceInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var CollectionFactory
     */
    protected $_agreementCollectionFactory;

    /**
     * @param RequestInterface $request
     * @param CollectionFactory $_agreementCollectionFactory
     */
    public function __construct(
        RequestInterface $request,
        CollectionFactory     $_agreementCollectionFactory
    )
    {
        $this->request = $request;
        $this->_agreementCollectionFactory = $_agreementCollectionFactory;
    }

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        $array = [
            ['value' => 0, 'label' => __('Store with normal registration term')],
        ];

        /** @var \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection $agreements */
        $agreements = $this->_agreementCollectionFactory->create();
        $agreements->addStoreFilter($this->getAdminStoreId());
        $agreements->addFieldToFilter('is_active', 1);
        $agreementsType[] = [
            ['eq' => 'loyalty']
        ];
        $agreements->addFieldToFilter('agreement_type', $agreementsType);

        if ($agreements->getSize() > 0) {
            $array[] = ['value' => 1, 'label' => __('Store with loyalty term')];
        }

        return $array;
    }

    public function getAdminStoreId()
    {
        return ($this->request->getParam("store")) ? $this->request->getParam("store") : 0;

    }


}