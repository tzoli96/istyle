<?php
namespace Oander\HelloBankPayment\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Api\Data\CartInterface;
use Oander\HelloBankPayment\Enum\Attribute;
use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Oander\HelloBankPayment\Model\ResourceModel\Barems\CollectionFactory;

class BaremCheck extends AbstractHelper
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CollectionFactory
     */
    private $baremCollection;

    private $disAllowedBarems = [];

    public function __construct(
        CollectionFactory $baremCollection,
        ProductRepositoryInterface $productRepository,
        Context $context
    ) {
        $this->baremCollection = $baremCollection;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function checkItHasBarem(CartInterface $quote = null): bool
    {
        $grandTotal=$quote->getGrandTotal();
        $results = $this->fillterTotal($quote->getItems(), $grandTotal);
        return (is_array($results) && count($results) > 0) ? true : false;
    }

    /**
     * @param $disAllowedItems
     * @param $grandTotal
     * @return array
     */
    public function fillterTotal($items, $grandTotal)
    {
        $result = [];
        $disAllowedItems = $this->fillterTotalBarem($items);
        $collection =  $this->baremCollection->create()
            ->addFieldToFilter(BaremInterface::STATUS, ['eq' => BaremInterface::STATUS_ENABLED]);
        if($disAllowedItems)
        {
            $collection->addFieldToFilter(BaremInterface::ID, ['nin' => $disAllowedItems]);
        }

        foreach($collection as $item)
        {
            if($item->getData(BaremInterface::MINIMUM_PRICE) <= $grandTotal)
            {
                $result[] = $item->getData();
            }
        }
        return $result;
    }

    /**
     * @param $collection
     * @return array
     */
    private function fillterTotalBarem($collection)
    {
        foreach($collection as $item){
            $currentDisAllowedProductAttribute = $item->getProduct()->getData(Attribute::PRODUCT_BAREM_CODE);
            if($currentDisAllowedProductAttribute)
            {
                $explode = explode(",", $currentDisAllowedProductAttribute);
                $this->disAllowedBarems = array_merge($this->disAllowedBarems, $explode);
            }
        }
        return $this->disAllowedBarems;
    }
}