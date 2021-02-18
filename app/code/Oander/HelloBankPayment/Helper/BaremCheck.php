<?php
namespace Oander\HelloBankPayment\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Api\Data\CartInterface;
use Oander\HelloBankPayment\Enum\Attribute;

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

    /*
     * todo: elvileg quote->getItems->getProduct()->getCustomAttribute("attname..") is le lehet kérdezni ha etc/catalog_att.xml meg van adva, de nekem localon nem sikerült le kérdezni
    public function checkItHasBarem(CartInterface $quote = null): bool
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $response = false;

        foreach ($quote->getItems() as $item)
        {
            $logger->info($item->getProduct()->getAttributeText(Attribute::PRODUCT_BAREM_CODE));
            $logger->info($item->getProduct()->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE));
            $logger->info($item->getProduct()->getProductAttribute(Attribute::PRODUCT_BAREM_CODE));
            $logger->info($item->getProduct()->getData(Attribute::PRODUCT_BAREM_CODE));
            if (null !== $item->getProduct()->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE))
            {
                $response = true;
                break;
            }
        }

        return $response;
    }
    */
}