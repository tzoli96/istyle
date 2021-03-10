<?php
namespace Oander\HelloBankPayment\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Api\Data\CartInterface;
use Oander\HelloBankPayment\Enum\Attribute;
use Oander\HelloBankPayment\Api\Data\BaremInterface;

class BaremCheck extends AbstractHelper
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Context $context
    ) {
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

        foreach ($quote->getItems() as $item)
        {
            $baremAttribute = $this->productRepository
                ->get($item->getSku())
                ->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE);

            if (null !== $baremAttribute) {
                $response = true;
            }
        }

        return $response;
    }

    /**
     * @param $collection
     * @param $grandTotal
     * @return array
     */
    public function fillterTotal($collection,$grandTotal)
    {
        $result = [];
        foreach($collection as $item)
        {
            if($item->getData(BaremInterface::MINIMUM_PRICE) <= $grandTotal &&
                $item->getData(BaremInterface::MAXIMUM_PRICE) >= $grandTotal)
            {
                $result[] = $item;
            }
        }
        return $result;
    }
}