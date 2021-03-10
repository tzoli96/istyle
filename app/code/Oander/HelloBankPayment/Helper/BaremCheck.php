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
        $response = false;
        $grandTotal=$quote->getGrandTotal();
        $barems = [];
        foreach ($quote->getItems() as $item)
        {
            $productBarems = $item->getProduct()->getData(Attribute::PRODUCT_BAREM_CODE);
            if($productBarems)
            {
                foreach($this->fillterTotal($productBarems,$grandTotal) as $item)
                {
                    if(!in_array($item->getData(),$barems))
                        $barems [] = $item->getData();
                }
            }
        }

        if(null != $barems)
        {
            $response = true;
        }

        return $response;
    }

    /**
     * @param $disAllowedItems
     * @param $grandTotal
     * @return array
     */
    public function fillterTotal($disAllowedItems, $grandTotal)
    {
        $result = [];
        $collection =  $this->baremCollection->create()
            ->addFieldToFilter(BaremInterface::STATUS, ['eq' => BaremInterface::STATUS_ENABLED])
            ->addFieldToFilter(BaremInterface::ID, ['nin' => $disAllowedItems]);

        foreach($collection as $item)
        {
            $item->getData();
            if($item->getData(BaremInterface::MINIMUM_PRICE) <= $grandTotal &&
                $item->getData(BaremInterface::MAXIMUM_PRICE) >= $grandTotal)
            {
                $result[] = $item;
            }
        }
        return $result;
    }
}